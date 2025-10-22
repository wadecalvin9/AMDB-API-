<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use RuntimeException;

class TmdbService
{
    protected string $bearer;

    public function __construct()
    {
        $this->bearer = env('TMDB_API_KEY');
        if (empty($this->bearer)) {
            throw new RuntimeException('TMDb API key is missing in environment configuration.');
        }
    }

    /**
     * Discover popular movies from TMDb
     * @param int $page Page number (1 to 500)
     * @return array List of movies or error details
     * @throws InvalidArgumentException If page is invalid
     */
    public function discoverMovies(int $page = 1): array
    {
        if ($page < 1 || $page > 500) {
            throw new InvalidArgumentException('Page number must be between 1 and 500.');
        }

        return Cache::remember("tmdb_discover_page_$page", config('services.tmdb.cache_ttl', 60), function () use ($page) {
            try {
                $response = Http::timeout(10)->retry(3, 100)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->bearer,
                    'Accept' => 'application/json',
                ])->get('https://api.themoviedb.org/3/discover/movie', [
                    'language' => 'en-US',
                    'sort_by' => 'popularity.desc',
                    'page' => $page,
                ]);

                if ($response->status() === 429) {
                    Log::warning('TMDb API rate limit exceeded', ['page' => $page]);
                    return ['error' => 'Rate limit exceeded', 'status' => 429];
                }

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['results']) && is_array($data['results'])) {
                        return $data['results'];
                    }
                    Log::warning('TMDb API returned unexpected response structure', ['page' => $page, 'response' => $data]);
                    return [];
                }

                Log::warning('TMDb Discover API request failed', ['status' => $response->status(), 'page' => $page]);
                return ['error' => 'Failed to fetch movies', 'status' => $response->status()];
            } catch (\Exception $e) {
                Log::error('TMDb Discover API exception: ' . $e->getMessage(), ['page' => $page]);
                return ['error' => 'API request failed', 'message' => $e->getMessage()];
            }
        });
    }

    /**
     * Search for movies on TMDb
     * @param string $query Search query
     * @param int $page Page number (1 to 500)
     * @return array List of movies or error details
     * @throws InvalidArgumentException If query or page is invalid
     */
    public function searchMovies(string $query, int $page = 1): array
    {
        $query = trim($query);
        if (empty($query)) {
            throw new InvalidArgumentException('Search query cannot be empty.');
        }
        if ($page < 1 || $page > 500) {
            throw new InvalidArgumentException('Page number must be between 1 and 500.');
        }

        return Cache::remember("tmdb_search_{$query}_page_$page", config('services.tmdb.cache_ttl', 60), function () use ($query, $page) {
            try {
                $response = Http::timeout(10)->retry(3, 100)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->bearer,
                    'Accept' => 'application/json',
                ])->get('https://api.themoviedb.org/3/search/movie', [
                    'language' => 'en-US',
                    'query' => $query,
                    'page' => $page,
                    'include_adult' => false,
                ]);

                if ($response->status() === 429) {
                    Log::warning('TMDb API rate limit exceeded', ['query' => $query, 'page' => $page]);
                    return ['error' => 'Rate limit exceeded', 'status' => 429];
                }

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['results']) && is_array($data['results'])) {
                        return $data['results'];
                    }
                    Log::warning('TMDb API returned unexpected response structure', ['query' => $query, 'page' => $page, 'response' => $data]);
                    return [];
                }

                Log::warning('TMDb Search API request failed', ['status' => $response->status(), 'query' => $query, 'page' => $page]);
                return ['error' => 'Failed to fetch movies', 'status' => $response->status()];
            } catch (\Exception $e) {
                Log::error('TMDb Search API exception: ' . $e->getMessage(), ['query' => $query, 'page' => $page]);
                return ['error' => 'API request failed', 'message' => $e->getMessage()];
            }
        });
    }

    /**
     * Get details for a specific movie
     * @param string $id TMDb movie ID
     * @return array|null Movie details or null/error details on failure
     * @throws InvalidArgumentException If ID is invalid
     */
    public function getMovieDetails(string $id): ?array
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('Invalid movie ID.');
        }

        return Cache::remember("tmdb_movie_{$id}", config('services.tmdb.cache_ttl', 60), function () use ($id) {
            try {
                $response = Http::timeout(10)->retry(3, 100)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->bearer,
                    'Accept' => 'application/json',
                ])->get("https://api.themoviedb.org/3/movie/{$id}", [
                    'language' => 'en-US',
                ]);

                if ($response->status() === 429) {
                    Log::warning('TMDb API rate limit exceeded', ['movie_id' => $id]);
                    return ['error' => 'Rate limit exceeded', 'status' => 429];
                }

                if ($response->successful()) {
                    $data = $response->json();
                    return is_array($data) ? $data : null;
                }

                Log::warning('TMDb movie details request failed', ['status' => $response->status(), 'movie_id' => $id]);
                return null;
            } catch (\Exception $e) {
                Log::error('TMDb movie details API exception: ' . $e->getMessage(), ['movie_id' => $id]);
                return ['error' => 'API request failed', 'message' => $e->getMessage()];
            }
        });
    }
}
