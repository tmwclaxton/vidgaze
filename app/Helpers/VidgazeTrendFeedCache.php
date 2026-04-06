<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

/**
 * Per-trend video lists for the home page (from Twitter trend search runs). Replaced when a run yields new topics.
 */
class VidgazeTrendFeedCache
{
    public const REDIS_KEY = 'vidgaze:trend_feed_manifest';

    public static function trendKey(string $trendQuery): string
    {
        return substr(hash('sha256', strtolower(trim($trendQuery))), 0, 16);
    }

    /**
     * @param  list<array{key: string, label: string, video_ids: list<int>}>  $topics
     */
    public static function replaceManifest(array $topics, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= (int) config('vidgaze.trend_picks.ttl_seconds', 172800);
        $payload = [
            'updated_at' => now()->toIso8601String(),
            'topics' => array_values($topics),
        ];
        Redis::setex(self::REDIS_KEY, $ttlSeconds, json_encode($payload));
    }

    /**
     * @return array{updated_at: string, topics: list<array{key: string, label: string, video_ids: list<int>}>}|null
     */
    public static function getManifest(): ?array
    {
        $raw = Redis::get(self::REDIS_KEY);
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['topics']) || ! is_array($decoded['topics'])) {
            return null;
        }

        return $decoded;
    }

    /**
     * @return list<int>
     */
    public static function getVideoIdsForKey(string $key): array
    {
        if (! preg_match('/^[a-f0-9]{16}$/', $key)) {
            return [];
        }
        $manifest = self::getManifest();
        if ($manifest === null) {
            return [];
        }
        foreach ($manifest['topics'] as $topic) {
            if (($topic['key'] ?? '') === $key && isset($topic['video_ids']) && is_array($topic['video_ids'])) {
                return array_values(array_filter(array_map('intval', $topic['video_ids']), fn (int $id) => $id > 0));
            }
        }

        return [];
    }
}
