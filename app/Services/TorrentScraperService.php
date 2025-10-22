<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;

class TorrentScraperService
{
    protected $client;
    protected $tpbBaseUrl;
    protected $ytsBaseUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
        ]);
        $this->tpbBaseUrl = env('TPB_BASE_URL', 'https://thepiratebay.org');
        $this->ytsBaseUrl = env('YTS_BASE_URL', 'https://yts.mx');
    }

    /**
     * Scrape torrents from The Pirate Bay or YTS for a given movie title
     * @param string $title Movie title (e.g., "The Lost Princess")
     * @param string $type Media type: 'movie' or 'series'
     * @return array Array of torrents (title, magnet, seeders, leechers, size)
     */
    public function getTorrents(string $title, string $type = 'movie'): array
    {
        if (!in_array($type, ['movie', 'series'])) {
            throw new InvalidArgumentException("Invalid type: $type. Must be 'movie' or 'series'.");
        }

        // Try TPB first
        $torrents = $this->scrapeTpb($title);
        if (!empty($torrents)) {
            return $torrents;
        }

        // Fallback to YTS for movies
        if ($type === 'movie') {
            $torrents = $this->scrapeYts($title);
        }

        if (empty($torrents)) {
            Log::info('No torrents found for title', ['title' => $title]);
        }

        return $torrents;
    }

    private function scrapeTpb(string $title): array
    {
        $queries = [$title, $title . ' ' . date('Y')];
        $torrents = [];

        foreach ($queries as $query) {
            $url = "{$this->tpbBaseUrl}/search/" . urlencode($query) . "/0/99/0";
            try {
                // Use Browsershot to render JavaScript
                $html = Browsershot::url($url)
                    ->waitUntilNetworkIdle()
                    ->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0')
                    ->timeout(15)
                    ->html();

                $crawler = new Crawler($html);
                if ($crawler->filter('table#searchResult')->count() === 0) {
                    Log::warning('No searchResult table found', ['url' => $url, 'html_snippet' => substr($html, 0, 500)]);
                    continue;
                }

                $crawler->filter('table#searchResult tr')->each(function (Crawler $row) use (&$torrents, $url) {
                    if ($row->filter('td')->count() === 0) {
                        return;
                    }

                    try {
                        $titleNode = $row->filter('td:nth-child(2) a.detLink');
                        $title = $titleNode->count() ? trim($titleNode->text()) : 'Unknown';

                        $magnetNode = $row->filter('td:nth-child(2) a[title="Download this torrent using magnet"]');
                        $magnet = $magnetNode->count() ? $magnetNode->attr('href') : '';

                        $descNode = $row->filter('td:nth-child(2) font.detDesc');
                        $size = $descNode->count() ? $this->extractSize($descNode->text()) : 'Unknown';

                        $seeders = $row->filter('td:nth-child(3)')->count() ? (int) $row->filter('td:nth-child(3)')->text() : 0;
                        $leechers = $row->filter('td:nth-child(4)')->count() ? (int) $row->filter('td:nth-child(4)')->text() : 0;

                        if ($magnet && $seeders > 0) {
                            $torrents[] = [
                                'title' => $title,
                                'url' => $magnet,
                                'seeders' => $seeders,
                                'leechers' => $leechers,
                                'size' => $size,
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to parse torrent row: {$e->getMessage()}", ['url' => $url]);
                    }
                });

                if (!empty($torrents)) {
                    break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to scrape TPB: {$e->getMessage()}", ['url' => $url]);
            }
        }

        return $torrents;
    }

    private function scrapeYts(string $title): array
    {
        $url = "{$this->ytsBaseUrl}/api/v2/list_movies.json?query_term=" . urlencode($title);
        try {
            $response = $this->client->get($url);
            $data = $response->json();

            $torrents = [];
            if (isset($data['data']['movies'][0]['torrents'])) {
                foreach ($data['data']['movies'][0]['torrents'] as $torrent) {
                    $torrents[] = [
                        'title' => $torrent['quality'] . ' ' . $torrent['type'],
                        'url' => $torrent['url'],
                        'seeders' => (int) ($torrent['seeds'] ?? 0),
                        'leechers' => (int) ($torrent['peers'] ?? 0),
                        'size' => $torrent['size'] ?? 'Unknown',
                    ];
                }
            }

            return $torrents;
        } catch (RequestException $e) {
            Log::error("Failed to scrape YTS: {$e->getMessage()}", ['url' => $url]);
            return [];
        }
    }

    private function extractSize(string $desc): string
    {
        preg_match('/Size\s+([\d\.]+\s*[A-Za-z]+)/', $desc, $matches);
        return $matches[1] ?? 'Unknown';
    }
}
