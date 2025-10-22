<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Addon;

class MovieController extends Controller
{
    private $tmdbBaseUrl = 'https://api.themoviedb.org/3';

    /**
     * Discover Movies or TV Shows
     */
    public function discover(Request $request)
    {
        $request->validate([
            'type' => 'in:movie,tv',
            'sort_by' => 'in:popularity.desc,popularity.asc,vote_average.desc,vote_average.asc,release_date.desc,release_date.asc,first_air_date.desc,first_air_date.asc',
            'page' => 'integer|min:1|max:500',
            'language' => 'regex:/^[a-z]{2}-[A-Z]{2}$/',
            'with_genres' => 'nullable|integer',
            'query' => 'nullable|string|max:100',
        ]);

        $type = $request->input('type', 'movie');
        $apiKey = config('services.tmdb.key');

        if (empty($apiKey)) {
            Log::error('TMDb API key not configured');
            return response()->json(['error' => 'TMDb API configuration error'], 500);
        }

        $language = $request->input('language', 'en-US');
        $page = $request->input('page', 1);
        $query = trim($request->input('query', ''));
        $sort_by = $request->input('sort_by', 'popularity.desc');
        $with_genres = $request->input('with_genres', '');

        // Fix mixed sort_by types
        if ($type === 'tv' && str_contains($sort_by, 'release_date')) {
            $sort_by = 'first_air_date.desc';
        } elseif ($type === 'movie' && str_contains($sort_by, 'first_air_date')) {
            $sort_by = 'release_date.desc';
        }

        // Build endpoint
        if (!empty($query)) {
            // Use TMDb multi search
            $endpoint = "{$this->tmdbBaseUrl}/search/multi";
            $params = [
                'api_key' => $apiKey,
                'language' => $language,
                'query' => $query,
                'page' => $page,
                'include_adult' => false,
            ];
        } else {
            $endpoint = "{$this->tmdbBaseUrl}/discover/{$type}";
            $params = [
                'api_key' => $apiKey,
                'language' => $language,
                'sort_by' => $sort_by,
                'page' => $page,
                'with_genres' => $with_genres,
                'include_adult' => false,
            ];
            if ($sort_by === 'release_date.desc') $params['primary_release_date.lte'] = now()->toDateString();
            elseif ($sort_by === 'first_air_date.desc') $params['first_air_date.lte'] = now()->toDateString();
        }

        $response = Http::get($endpoint, $params);
        $data = $response->json();

        // Fallback to popular if empty
        if (!$response->ok() || empty($data['results'])) {
            $fallback = Http::get("{$this->tmdbBaseUrl}/{$type}/popular", [
                'api_key' => $apiKey,
                'language' => $language,
            ]);
            $data = $fallback->json();
        }

        $results = collect($data['results'] ?? []);

        // Sort search results by popularity
        if (!empty($query)) {
            $results = $results->sortByDesc('popularity');
        }

        $currentYear = now()->year;
        $filtered = $results->filter(function ($item) use ($currentYear, $type, $query) {
            $mediaType = $item['media_type'] ?? $type;
            if (!in_array($mediaType, ['movie', 'tv'])) return false;

            $date = $item['release_date'] ?? $item['first_air_date'] ?? null;
            $title = $item['title'] ?? $item['name'] ?? '';
            $poster = $item['poster_path'] ?? null;
            $vote = $item['vote_average'] ?? 0;
            $overview = $item['overview'] ?? '';

            // 🧠 Preserve actual type for correct routing later
            $item['resolved_type'] = $mediaType;

            // If searching — apply stricter filtering
            if (!empty($query)) {
                if (!$title || !$poster) return false;
                if (($item['id'] ?? 0) < 1000) return false;
                if ($vote <= 0) return false; // skip unrated
                if (($item['popularity'] ?? 0) < 5) return false; // skip unpopular junk
                if (strlen($overview) < 20) return false;

                $q = strtolower($query);
                $nameMatch = str_contains(strtolower($title), $q)
                    || str_contains(strtolower($item['original_title'] ?? $item['original_name'] ?? ''), $q);
                if (!$nameMatch) return false;

                return true;
            }

            // Normal discovery filter
            if (!$title || !$date) return false;
            if (intval(substr($date, 0, 4)) > $currentYear) return false;
            if (($item['id'] ?? 0) < 1000) return false;
            if ($vote <= 0) return false; // ❌ skip all unrated items

            return true;
        });

        // Cache genres
        $genres = Cache::remember("tmdb_{$type}_genres_{$language}", now()->addHours(24), function () use ($apiKey, $language, $type) {
            $res = Http::get("https://api.themoviedb.org/3/genre/{$type}/list", [
                'api_key' => $apiKey,
                'language' => $language,
            ]);
            return $res->successful() ? $res->json()['genres'] ?? [] : [];
        });

        // ✅ Preserve resolved type in results
        $movies = $filtered->values()->map(function ($m) use ($type) {
            $m['resolved_type'] = $m['resolved_type'] ?? $type;
            return $m;
        })->all();

        return view('movies.index', [
            'movies' => $movies,
            'genres' => $genres,
            'type' => $type,
            'sort_by' => $sort_by,
            'with_genres' => $with_genres,
            'language' => $language,
            'pagination' => [
                'current_page' => $data['page'] ?? 1,
                'total_pages' => $data['total_pages'] ?? 1,
            ],
        ]);
    }

    /**
     * Show details + fetch correct stream ID
     */
    public function show(Request $request, $id)
    {
        $type = in_array($request->input('type'), ['movie', 'tv']) ? $request->input('type') : 'movie';
        $apiKey = config('services.tmdb.key');

        $endpoint = "{$this->tmdbBaseUrl}/{$type}/{$id}";
        $response = Http::get($endpoint, [
            'api_key' => $apiKey,
            'language' => $request->input('language', 'en-US'),
            'append_to_response' => 'videos,credits,recommendations,images,external_ids',
        ]);

        if (!$response->successful()) {
            return redirect()->route('discover')->with('error', 'Failed to fetch details.');
        }

        $item = $response->json();
        $external = $item['external_ids'] ?? [];
        $imdbId = $external['imdb_id'] ?? null;

        // Try AniList fallback for anime-like TV shows
        if (!$imdbId && $type === 'tv') {
            $animeMatch = $this->resolveAnimeId($item['name'] ?? $item['original_name'] ?? '');
            if ($animeMatch) $imdbId = $animeMatch;
        }

        $streams = [];
        if ($type === 'movie') {
            $streams = $this->fetchStreams('movie', $imdbId ?? "tmdb:$id");
        } elseif ($type === 'tv') {
            $season = $request->input('season', $item['seasons'][0]['season_number'] ?? 1);
            $episodes = $this->fetchEpisodes($id, $season, $apiKey);

            return view('movies.show', [
                'movie' => $item,
                'type' => 'tv',
                'seasons' => $item['seasons'] ?? [],
                'episodes' => $episodes,
                'selectedSeason' => $season,
                'imdbId' => $imdbId,
                'streams' => $streams,
            ]);
        }

        return view('movies.show', [
            'movie' => $item,
            'type' => 'movie',
            'streams' => $streams,
        ]);
    }

    private function resolveAnimeId(string $title): ?string
    {
        if (!$title) return null;

        try {
            $query = '
            query ($search: String) {
                Media(search: $search, type: ANIME) {
                    id
                    idMal
                }
            }';
            $response = Http::post('https://graphql.anilist.co', [
                'query' => $query,
                'variables' => ['search' => $title],
            ]);

            $data = $response->json('data.Media');
            if ($data) {
                if (!empty($data['id'])) return 'anilist:' . $data['id'];
                if (!empty($data['idMal'])) return 'mal:' . $data['idMal'];
            }
        } catch (\Exception $e) {
            Log::warning("AniList lookup failed for $title: " . $e->getMessage());
        }

        return null;
    }

    private function fetchEpisodes($tvId, $season, $apiKey)
    {
        $response = Http::get("{$this->tmdbBaseUrl}/tv/{$tvId}/season/{$season}", [
            'api_key' => $apiKey,
        ]);
        return $response->successful() ? ($response->json()['episodes'] ?? []) : [];
    }

    private function fetchStreams($type, $id)
    {
        $addons = Addon::all();
        if ($addons->isEmpty()) return [];

        $streams = [];
        foreach ($addons as $addon) {
            $baseUrl = preg_replace('/\/manifest\.json$/', '', $addon->manifest_url);
            $url = "{$baseUrl}/stream/{$type}/{$id}.json";
            try {
                $res = Http::timeout(10)->get($url);
                if ($res->ok()) {
                    $addonStreams = $res->json('streams', []);
                    foreach ($addonStreams as &$s) {
                        $s['addon'] = $addon->name;
                    }
                    $streams = array_merge($streams, $addonStreams);
                }
            } catch (\Exception $e) {
                Log::warning("Addon fetch failed: {$addon->name}", ['error' => $e->getMessage()]);
            }
        }

        return array_values($streams);
    }

    public function clientStream($type, $id)
    {
        return response()->json(['streams' => $this->fetchStreams($type, $id)]);
    }
}
