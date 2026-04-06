<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twitter/X trend topics via Apify actor (default: karamelo~twitter-trends-scraper).
 */
class ApifyTwitterTrends
{
    /**
     * Run actor sync and return dataset items (raw rows from Apify).
     */
    public static function fetchDatasetItems(?array $input = null, ?int $timeoutSeconds = null): array
    {
        $token = config('services.apify.token');
        $actor = config('services.apify.twitter_trends_actor');
        if (! $token || ! $actor) {
            Log::warning('Apify Twitter trends: set APIFY_TOKEN and APIFY_TWITTER_TRENDS_ACTOR.');

            return [];
        }

        $timeoutSeconds ??= (int) config('services.apify.twitter_trends_timeout', 180);
        $actorEncoded = str_replace('/', '~', $actor);
        $url = "https://api.apify.com/v2/acts/{$actorEncoded}/run-sync-get-dataset-items";

        $body = $input ?? config('services.apify.twitter_trends_input', []);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeoutSeconds)
                ->connectTimeout(30)
                ->post($url, $body);
        } catch (\Throwable $e) {
            Log::warning('Apify Twitter trends request error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Apify Twitter trends HTTP error', [
                'status' => $response->status(),
                'snippet' => substr($response->body(), 0, 800),
            ]);

            return [];
        }

        $json = $response->json();
        if ($json === null) {
            return [];
        }

        if (isset($json['error'])) {
            Log::warning('Apify Twitter trends API error', ['error' => $json['error']]);

            return [];
        }

        if (is_array($json) && array_is_list($json)) {
            return $json;
        }

        if (isset($json['data']) && is_array($json['data']) && array_is_list($json['data'])) {
            return $json['data'];
        }

        return [];
    }

    /**
     * @return list<string> deduplicated, normalized search-sized strings
     */
    public static function fetchTrendStrings(?array $input = null, ?int $timeoutSeconds = null): array
    {
        $items = self::fetchDatasetItems($input, $timeoutSeconds);
        $out = [];
        foreach ($items as $item) {
            foreach (self::stringsFromItem($item) as $s) {
                $n = self::normalizeTrendText($s);
                if ($n !== '') {
                    $out[$n] = true;
                }
            }
        }

        return array_keys($out);
    }

    /**
     * @return list<string>
     */
    private static function stringsFromItem(mixed $item): array
    {
        $found = [];
        if (is_string($item)) {
            $found[] = $item;

            return $found;
        }
        if (! is_array($item)) {
            return $found;
        }

        $keys = ['name', 'trend', 'trendName', 'trend_name', 'topic', 'hashtag', 'text', 'title', 'query', 'keyword', 'Trend', 'Name'];
        foreach ($keys as $k) {
            if (! empty($item[$k]) && is_string($item[$k])) {
                $found[] = $item[$k];
            }
        }

        foreach ($item as $v) {
            if (is_string($v) && ! in_array($v, $found, true)) {
                $found[] = $v;
            }
            if (is_array($v)) {
                foreach (self::stringsFromItem($v) as $s) {
                    $found[] = $s;
                }
            }
        }

        return $found;
    }

    private static function normalizeTrendText(string $raw): string
    {
        $t = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $t = ltrim($t, '#');
        $t = preg_replace('/\s+/', ' ', $t) ?? $t;

        if (strlen($t) < 2 || strlen($t) > 120) {
            return '';
        }

        return $t;
    }
}
