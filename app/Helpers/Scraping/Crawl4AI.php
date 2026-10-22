<?php

namespace App\Helpers\Scraping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin client for the self hosted crawl4ai server (POST /crawl).
 *
 * Configure with CRAWL4AI_URL (for example http://crawl4ai:11235 when the app
 * container is attached to crawl4ai's docker network) and CRAWL4AI_API_TOKEN.
 * When CRAWL4AI_URL is empty the client is disabled and callers fall back to
 * their old provider.
 */
class Crawl4AI
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected ?string $token = null,
        protected ?int $timeout = null,
        protected ?int $pageTimeoutMs = null,
        protected ?string $cacheMode = null,
    ) {
        $this->baseUrl = rtrim((string) ($baseUrl ?? config('services.crawl4ai.url')), '/');
        $this->token = $token ?? config('services.crawl4ai.token');
        $this->timeout = $timeout ?? (int) config('services.crawl4ai.timeout', 90);
        $this->pageTimeoutMs = $pageTimeoutMs ?? (int) config('services.crawl4ai.page_timeout_ms', 30000);
        $this->cacheMode = $cacheMode ?? (string) config('services.crawl4ai.cache_mode', 'bypass');
    }

    public static function enabled(): bool
    {
        return (bool) config('services.crawl4ai.enabled', true) && !empty(config('services.crawl4ai.url'));
    }

    /**
     * Fetch the rendered HTML of one page, or null on any failure.
     */
    public function html(string $url, array $extra = []): ?string
    {
        return $this->htmlMany([$url], $extra)[$url] ?? null;
    }

    /**
     * Fetch several pages in one crawl4ai request.
     *
     * @param  string[]  $urls
     * @param  array  $extra  extra top level request fields, for example
     *                        ['site_profile' => 'youtube'] (opt in, youtube.com only)
     * @return array<string, string|null>  keyed by the requested url
     */
    public function htmlMany(array $urls, array $extra = []): array
    {
        $urls = array_values(array_unique($urls));
        $out = array_fill_keys($urls, null);
        if (!$urls || !$this->baseUrl) {
            return $out;
        }

        try {
            $request = Http::timeout($this->timeout)->acceptJson();
            if ($this->token) {
                $request = $request->withToken($this->token);
            }
            $response = $request->post($this->baseUrl . '/crawl', $extra + [
                'urls' => $urls,
                'crawler_config' => [
                    'type' => 'CrawlerRunConfig',
                    'params' => [
                        'cache_mode' => $this->cacheMode,
                        'page_timeout' => $this->pageTimeoutMs,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::warning('crawl4ai request failed', ['status' => $response->status(), 'urls' => $urls]);
                return $out;
            }

            foreach ((array) $response->json('results', []) as $i => $result) {
                $key = $result['url'] ?? ($urls[$i] ?? null);
                if (!array_key_exists($key, $out)) {
                    $key = $urls[$i] ?? $key;
                }
                $status = (int) ($result['status_code'] ?? 200);
                if (!empty($result['success']) && $status < 400 && !empty($result['html'])) {
                    $out[$key] = $result['html'];
                } else {
                    Log::warning('crawl4ai page failed', [
                        'url' => $key,
                        'status' => $status,
                        'error' => mb_substr((string) ($result['error_message'] ?? ''), 0, 200),
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::warning('crawl4ai unreachable: ' . $e->getMessage());
        }

        return $out;
    }
}
