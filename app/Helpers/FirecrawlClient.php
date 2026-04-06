<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirecrawlClient
{
    public function __construct(
        protected ?string $apiKey = null,
    ) {
        $this->apiKey = $apiKey ?? (string) config('services.firecrawl.api_key');
    }

    public static function make(): ?self
    {
        $key = config('services.firecrawl.api_key');
        if ($key === null || $key === '') {
            return null;
        }

        return new self((string) $key);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function search(string $query, int $limit = 10, array $scrapeOptions = [], int $timeoutSeconds = 120): array
    {
        $payload = [
            'query' => $query,
            'limit' => min(max($limit, 1), 100),
        ];
        if ($scrapeOptions !== []) {
            $payload['scrapeOptions'] = $scrapeOptions;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeoutSeconds)
                ->connectTimeout(25)
                ->post('https://api.firecrawl.dev/v1/search', $payload);
        } catch (\Throwable $e) {
            Log::warning('Firecrawl search transport error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Firecrawl search HTTP error', ['status' => $response->status()]);

            return [];
        }

        $json = $response->json();
        if (empty($json['success'])) {
            Log::warning('Firecrawl search unsuccessful', ['json' => $json]);

            return [];
        }

        $data = $json['data'] ?? [];

        return is_array($data) ? $data : [];
    }

    /**
     * @param  list<string>  $formats
     * @return array<string, mixed>|null
     */
    public function scrape(string $url, array $formats = ['markdown'], int $timeoutSeconds = 120): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeoutSeconds)
                ->connectTimeout(25)
                ->post('https://api.firecrawl.dev/v1/scrape', [
                    'url' => $url,
                    'formats' => $formats,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Firecrawl scrape transport error: '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Firecrawl scrape HTTP error', ['status' => $response->status(), 'url' => $url]);

            return null;
        }

        $json = $response->json();
        if (empty($json['success']) || ! is_array($json['data'] ?? null)) {
            return null;
        }

        return $json['data'];
    }
}
