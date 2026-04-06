<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CategoryFeedViewerCooldown
{
    public static function viewerKey(?int $creatorViewerId, ?string $feedClientUuid, int $categoryId): string
    {
        $bucket = 'anon';
        if ($creatorViewerId !== null && $creatorViewerId > 0) {
            $bucket = 'u:'.$creatorViewerId;
        } elseif ($feedClientUuid !== null && self::isUuid($feedClientUuid)) {
            $bucket = 'g:'.$feedClientUuid;
        }

        return "vidgaze:catfeed_recent:{$bucket}:{$categoryId}";
    }

    /**
     * @return list<int>
     */
    public static function getRecent(?int $creatorViewerId, ?string $feedClientUuid, int $categoryId): array
    {
        $key = self::viewerKey($creatorViewerId, $feedClientUuid, $categoryId);
        $raw = Redis::lrange($key, 0, -1);
        if ($raw === null || $raw === []) {
            return [];
        }
        $out = [];
        foreach ($raw as $item) {
            $id = (int) $item;
            if ($id > 0) {
                $out[] = $id;
            }
        }

        return $out;
    }

    /**
     * @param  list<int>  $returnedVideoIds
     */
    public static function pushRecent(?int $creatorViewerId, ?string $feedClientUuid, int $categoryId, array $returnedVideoIds): void
    {
        $returnedVideoIds = array_values(array_unique(array_filter(array_map('intval', $returnedVideoIds), fn (int $id) => $id > 0)));
        if ($returnedVideoIds === []) {
            return;
        }
        $key = self::viewerKey($creatorViewerId, $feedClientUuid, $categoryId);
        $window = max(1, (int) config('vidgaze.category_discovery.recent_video_window', 40));
        $ttl = (int) config('vidgaze.category_discovery.recent_ttl_seconds', 604800);
        foreach (array_reverse($returnedVideoIds) as $id) {
            Redis::lpush($key, (string) $id);
        }
        Redis::ltrim($key, 0, $window - 1);
        Redis::expire($key, $ttl);
    }

    private static function isUuid(string $value): bool
    {
        return Str::isUuid($value);
    }
}
