<?php

namespace App\Console\Commands;

use App\Helpers\ApifyYoutubeActorAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Dev-only: POST run-sync-get-dataset-items for streamers vs apidojo YouTube actors.
 * Writes JSON under storage/app/apify_youtube_matrix/, SUMMARY.json, and gap_report.json.
 */
class CompareYoutubeApifyActors extends Command
{
    protected $signature = 'youtube:compare-apify-actors
                            {--timeout=120 : HTTP timeout per request (seconds)}
                            {--channel= : UC… id for channel tests (default: Kurzgesagt)}';

    protected $description = 'Compare Apify YouTube actors (streamers vs apidojo); saves artifacts to storage/app/apify_youtube_matrix';

    private const STREAMERS = 'streamers~youtube-scraper';

    private const APIDOJO = 'apidojo~youtube-scraper';

    public function handle(): int
    {
        $token = (string) config('services.apify.token');
        if ($token === '') {
            $this->error('APIFY_TOKEN is not set.');

            return self::FAILURE;
        }

        $dir = storage_path('app/apify_youtube_matrix');
        File::ensureDirectoryExists($dir);

        $timeout = (int) $this->option('timeout');
        $channelId = $this->option('channel') ?: 'UCsXVk37bltHxD1rDPwtNM8Q';
        $channelUrl = 'https://www.youtube.com/channel/'.$channelId;
        $videosUrl = $channelUrl.'/videos';
        $watchUrl = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        $matrix = [
            'search_keywords' => [
                'streamers' => ['searchQueries' => ['laravel tutorial'], 'maxResults' => 15],
                'apidojo' => ['keywords' => ['laravel tutorial'], 'maxItems' => 15, 'gl' => 'us', 'hl' => 'en'],
            ],
            'search_low_max' => [
                'streamers' => ['searchQueries' => ['cats'], 'maxResults' => 5],
                'apidojo' => ['keywords' => ['cats'], 'maxItems' => 5, 'gl' => 'us', 'hl' => 'en'],
            ],
            'channel_videos_tab' => [
                'streamers' => ['startUrls' => [['url' => $videosUrl]], 'maxResults' => 25, 'sortVideosBy' => 'NEWEST'],
                'apidojo' => ['startUrls' => [$videosUrl], 'maxItems' => 25, 'gl' => 'us', 'hl' => 'en'],
            ],
            'channel_home_uc' => [
                'streamers' => ['startUrls' => [['url' => $channelUrl]], 'maxResults' => 12],
                'apidojo' => ['startUrls' => [$channelUrl], 'maxItems' => 25, 'gl' => 'us', 'hl' => 'en'],
            ],
            'channel_home_handle' => [
                'streamers' => ['startUrls' => [['url' => 'https://www.youtube.com/@kurzgesagt']], 'maxResults' => 12],
                'apidojo' => ['startUrls' => ['https://www.youtube.com/@kurzgesagt'], 'maxItems' => 25, 'gl' => 'us', 'hl' => 'en'],
            ],
            'watch_urls_small' => [
                'streamers' => ['startUrls' => [['url' => $watchUrl]], 'maxResults' => 8],
                'apidojo' => ['startUrls' => [$watchUrl], 'maxItems' => 8, 'gl' => 'us', 'hl' => 'en'],
            ],
            'watch_urls_three' => [
                'streamers' => [
                    'startUrls' => [
                        ['url' => $watchUrl],
                        ['url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0'],
                        ['url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw'],
                    ],
                    'maxResults' => 12,
                ],
                'apidojo' => [
                    'startUrls' => [
                        $watchUrl,
                        'https://www.youtube.com/watch?v=9bZkp7q19f0',
                        'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                    ],
                    'maxItems' => 12,
                    'gl' => 'us',
                    'hl' => 'en',
                ],
            ],
        ];

        $summary = [
            'run_at' => now()->toIso8601String(),
            'cases' => [],
        ];

        foreach ($matrix as $case => $payloads) {
            $this->line("Case: <info>{$case}</info>");
            foreach (['streamers', 'apidojo'] as $family) {
                $actor = $family === 'streamers' ? self::STREAMERS : self::APIDOJO;
                $body = $payloads[$family];
                $path = "{$dir}/{$case}_{$family}.json";
                $result = $this->runSync($token, $actor, $body, $timeout);
                File::put($path, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $items = $result['items'] ?? [];
                $count = is_array($items) ? count($items) : 0;
                $http = $result['http_status'] ?? null;
                $err = $result['error'] ?? null;
                $this->line("  {$family}: HTTP {$http}, items={$count}".($err ? ', error='.json_encode($err) : ''));
                $summary['cases'][$case][$family] = [
                    'http_status' => $http,
                    'item_count' => $count,
                    'error' => $err,
                    'first_item_keys' => ($count > 0 && is_array($items[0])) ? array_keys($items[0]) : [],
                ];
            }
        }

        File::put($dir.'/SUMMARY.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Wrote '.$dir.'/SUMMARY.json');

        $summaryDecoded = json_decode(File::get($dir.'/SUMMARY.json'), true);
        $gap = ApifyYoutubeActorAdapter::gapReportFromMatrixSummary(is_array($summaryDecoded) ? $summaryDecoded : []);
        File::put($dir.'/gap_report.json', json_encode($gap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Wrote '.$dir.'/gap_report.json');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function runSync(string $token, string $actor, array $input, int $timeout): array
    {
        $actorPath = str_replace('/', '~', $actor);
        $url = "https://api.apify.com/v2/acts/{$actorPath}/run-sync-get-dataset-items";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeout)
                ->connectTimeout(30)
                ->post($url, $input);
        } catch (\Throwable $e) {
            return [
                'http_status' => 0,
                'error' => $e->getMessage(),
                'items' => [],
            ];
        }

        $status = $response->status();
        $json = $response->json();

        if ($json === null) {
            return [
                'http_status' => $status,
                'error' => 'invalid_json',
                'raw_snippet' => substr($response->body(), 0, 500),
                'items' => [],
            ];
        }

        if (isset($json['error'])) {
            return [
                'http_status' => $status,
                'error' => $json['error'],
                'items' => [],
            ];
        }

        $items = [];
        if (is_array($json) && array_is_list($json)) {
            $items = $json;
        } elseif (isset($json['data']) && is_array($json['data']) && array_is_list($json['data'])) {
            $items = $json['data'];
        }

        return [
            'http_status' => $status,
            'items' => $items,
        ];
    }
}
