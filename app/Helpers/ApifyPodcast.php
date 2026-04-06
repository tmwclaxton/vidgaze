<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Apple Podcasts data via Apify actor (default: automation-lab~podcast-scraper) — sync dataset fetch.
 */
class ApifyPodcast
{
    public static function syncDatasetItems(array $input, int $timeoutSeconds = 300): array
    {
        $token = config('services.apify.token');
        $actor = config('services.apify.podcast_actor');
        if (! $token || ! $actor) {
            Log::warning('Apify Podcast: set APIFY_TOKEN and APIFY_PODCAST_ACTOR.');

            return [];
        }

        $actorEncoded = str_replace('/', '~', $actor);
        $url = "https://api.apify.com/v2/acts/{$actorEncoded}/run-sync-get-dataset-items";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ])
                ->timeout($timeoutSeconds)
                ->connectTimeout(30)
                ->post($url, $input);
        } catch (\Throwable $e) {
            Log::warning('Apify Podcast sync request error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Apify Podcast sync HTTP error', [
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
            Log::warning('Apify Podcast sync API error', ['error' => $json['error']]);

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
