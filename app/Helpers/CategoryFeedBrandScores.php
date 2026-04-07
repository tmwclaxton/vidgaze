<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;
use Throwable;

class CategoryFeedBrandScores
{
    public static function redisKey(int $categoryId): string
    {
        return 'vidgaze:category_feed_brand:'.$categoryId;
    }

    public static function applyDecay(int $categoryId, ?float $factor = null): void
    {
        $factor ??= (float) config('vidgaze.category_discovery.brand_decay_factor', 0.92);
        if ($factor <= 0 || $factor >= 1) {
            return;
        }
        $key = self::redisKey($categoryId);
        $all = Redis::hgetall($key);
        if ($all === [] || $all === null) {
            return;
        }
        foreach ($all as $field => $val) {
            $new = max(0.0, (float) $val * $factor);
            Redis::hset($key, (string) $field, (string) round($new, 4));
        }
        $ttl = (int) config('vidgaze.category_discovery.ttl_seconds', 172800);
        Redis::expire($key, $ttl);
    }

    /**
     * Initialise scores for new IDs, delete hashes for IDs removed from the slot.
     *
     * @param  list<int>  $videoIds
     */
    public static function syncScoresForCategory(int $categoryId, array $videoIds): void
    {
        $videoIds = array_values(array_unique(array_filter(array_map('intval', $videoIds), fn (int $id) => $id > 0)));
        $key = self::redisKey($categoryId);
        $initial = (float) config('vidgaze.category_discovery.initial_brand_score', 100);
        $existing = Redis::hgetall($key);
        if ($existing === null) {
            $existing = [];
        }
        $want = array_fill_keys(array_map('strval', $videoIds), true);
        foreach ($existing as $field => $_) {
            if (! isset($want[$field])) {
                Redis::hdel($key, $field);
            }
        }
        foreach ($videoIds as $vid) {
            $f = (string) $vid;
            if (! Redis::hexists($key, $f)) {
                Redis::hset($key, $f, (string) $initial);
            }
        }
        $ttl = (int) config('vidgaze.category_discovery.ttl_seconds', 172800);
        Redis::expire($key, $ttl);
    }

    /**
     * @param  list<int>  $videoIds
     * @return array<int, float> video_id => score
     */
    public static function getScores(int $categoryId, array $videoIds): array
    {
        $initial = (float) config('vidgaze.category_discovery.initial_brand_score', 100);

        try {
            $key = self::redisKey($categoryId);
            $out = [];
            foreach ($videoIds as $vid) {
                $vid = (int) $vid;
                if ($vid < 1) {
                    continue;
                }
                $raw = Redis::hget($key, (string) $vid);
                $out[$vid] = $raw !== null && $raw !== false ? (float) $raw : $initial;
            }

            return $out;
        } catch (Throwable) {
            $out = [];
            foreach ($videoIds as $vid) {
                $vid = (int) $vid;
                if ($vid < 1) {
                    continue;
                }
                $out[$vid] = $initial;
            }

            return $out;
        }
    }

    public static function boostVideo(int $videoId, ?float $delta = null): void
    {
        if ($videoId < 1) {
            return;
        }
        $delta ??= (float) config('vidgaze.category_discovery.watch_boost_amount', 8);
        if ($delta <= 0) {
            return;
        }
        foreach (VidgazeCategoryFeedCache::categoryIdsForVideo($videoId) as $categoryId) {
            $key = self::redisKey($categoryId);
            if (Redis::hexists($key, (string) $videoId)) {
                Redis::hincrbyfloat($key, (string) $videoId, $delta);
                Redis::expire($key, (int) config('vidgaze.category_discovery.ttl_seconds', 172800));
            }
        }
    }
}
