<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

/**
 * Redis-backed supplemental video IDs for VidGaze Picks (Twitter trend discovery), merged in pinned API.
 */
class VidgazeTrendPickCache
{
    public const REDIS_KEY = 'vidgaze:trend_pick_video_ids';

    /**
     * @return list<int>
     */
    public static function getVideoIds(): array
    {
        $raw = Redis::get(self::REDIS_KEY);
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $decoded), fn (int $id) => $id > 0));
    }

    /**
     * @param  list<int>  $ids
     */
    public static function mergePush(array $ids, ?int $maxIds = null, ?int $ttlSeconds = null): void
    {
        $maxIds ??= (int) config('vidgaze.trend_picks.max_cached_video_ids', 48);
        $ttlSeconds ??= (int) config('vidgaze.trend_picks.ttl_seconds', 172800);

        $ids = array_values(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0));
        if ($ids === []) {
            return;
        }

        $existing = self::getVideoIds();
        $merged = array_values(array_unique(array_merge($ids, $existing)));
        $merged = array_slice($merged, 0, $maxIds);

        Redis::setex(self::REDIS_KEY, $ttlSeconds, json_encode($merged));
    }
}
