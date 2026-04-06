<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Apify "streamers/youtube-scraper" (actor id streamers~youtube-scraper) — sync dataset fetch.
 */
class ApifyYoutube
{
    public static function syncDatasetItems(array $input, int $timeoutSeconds = 600): array
    {
        $token = config('services.apify.token');
        $actor = config('services.apify.youtube_actor');
        if (! $token || ! $actor) {
            Log::warning('Apify YouTube: set APIFY_TOKEN and APIFY_YOUTUBE_ACTOR (or use default actor).');

            return [];
        }

        $url = "https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeoutSeconds)
                ->connectTimeout(30)
                ->post($url, $input);
        } catch (\Throwable $e) {
            Log::warning('Apify YouTube sync request error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Apify YouTube sync HTTP error', [
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
            Log::warning('Apify YouTube sync API error', ['error' => $json['error']]);

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
}
