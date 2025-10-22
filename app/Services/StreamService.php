<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class StreamService
{
    protected $endpoints;

    public function __construct()
    {
        // Prioritize ThePirateBay+, fallback to Torrentio
        $this->endpoints = array_filter([
            config('services.tpb.endpoint', 'https://thepiratebay-plus.strem.fun/manifest.json'),
            config('services.torrentio.endpoint'),
        ]);

        if (empty($this->endpoints)) {
            Log::warning('No stream add-ons configured. Set TPB_CONFIG_URL or TORRENTIO_CONFIG_URL in .env.');
        }
    }

    /**
     * Get streams from Stremio add-ons (ThePirateBay+, Torrentio)
     * @param string $id TMDb ID (for movies/series) or Kitsu ID (for anime), without prefix
     * @param string $type Media type: 'movie', 'series', or 'anime'
     * @return array Array of streams or empty array if unavailable
     * @throws InvalidArgumentException If type is invalid
     */
    public function getStreams(string $id, string $type = 'movie'): array
    {
        if (!in_array($type, ['movie', 'series', 'anime'])) {
            throw new InvalidArgumentException("Invalid type: $type. Must be 'movie', 'series', or 'anime'.");
        }

        $prefixedId = $type === 'anime' ? 'kitsu' . $id : 'tt' . $id;

        if (empty($this->endpoints)) {
            Log::info('No stream add-ons configured; skipping fetch', ['id' => $prefixedId, 'type' => $type]);
            return [];
        }

        foreach ($this->endpoints as $endpoint) {
            if (!str_contains($endpoint, '/manifest.json')) {
                Log::warning('Invalid add-on endpoint; must end with /manifest.json', ['endpoint' => $endpoint]);
                continue;
            }

            try {
                $streamEndpoint = str_replace('/manifest.json', '/stream', $endpoint);
                $response = Http::timeout(10)->retry(3, 100)->post($streamEndpoint, [
                    'id' => $prefixedId,
                    'type' => $type
                ]);

                if (app()->environment('local')) {
                    Log::info('Stream add-on request', [
                        'endpoint' => $streamEndpoint,
                        'id' => $prefixedId,
                        'type' => $type,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }

                if ($response->status() === 404 || $response->status() === 429) {
                    Log::warning('Stream add-on request failed', [
                        'status' => $response->status(),
                        'endpoint' => $streamEndpoint,
                        'id' => $prefixedId,
                        'type' => $type
                    ]);
                    continue; // Try next endpoint
                }

                if ($response->successful()) {
                    $data = $response->json();
                    $streams = is_array($data) && isset($data['streams']) && is_array($data['streams']) ? $data['streams'] : [];
                    if (empty($streams)) {
                        Log::info('No streams found from add-on', [
                            'endpoint' => $streamEndpoint,
                            'id' => $prefixedId,
                            'type' => $type,
                            'response_keys' => array_keys((array) $data)
                        ]);
                    }
                    return $streams; // Return first successful result
                }

                Log::warning("Stream add-on request failed: Status {$response->status()}", [
                    'endpoint' => $streamEndpoint,
                    'id' => $prefixedId,
                    'type' => $type
                ]);
            } catch (\Exception $e) {
                Log::error("Stream add-on exception: {$e->getMessage()}", [
                    'endpoint' => $streamEndpoint,
                    'id' => $prefixedId,
                    'type' => $type
                ]);
            }
        }

        Log::info('No streams available from any add-on', ['id' => $prefixedId, 'type' => $type]);
        return [];
    }
}
