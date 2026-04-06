<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

/**
 * Per-category AI search snippet + video list (from category discovery command).
 *
 * @phpstan-type CategoryEntry array{category_id: int, slug: string, name: string, label: string, video_ids: list<int>}
 */
class VidgazeCategoryFeedCache
{
    public const REDIS_KEY_MANIFEST = 'vidgaze:category_feed_manifest';

    public const REDIS_KEY_VIDEO_INDEX = 'vidgaze:category_feed_video_index';

    /**
     * @param  list<CategoryEntry>  $categories
     */
    public static function replaceManifest(array $categories, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= (int) config('vidgaze.category_discovery.ttl_seconds', 172800);
        $payload = [
            'updated_at' => now()->toIso8601String(),
            'categories' => array_values($categories),
        ];
        Redis::setex(self::REDIS_KEY_MANIFEST, $ttlSeconds, json_encode($payload));
        self::rebuildVideoCategoryIndex($payload['categories']);
    }

    /**
     * @return array{updated_at: string, categories: list<CategoryEntry>}|null
     */
    public static function getManifest(): ?array
    {
        $raw = Redis::get(self::REDIS_KEY_MANIFEST);
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['categories']) || ! is_array($decoded['categories'])) {
            return null;
        }

        return $decoded;
    }

    /**
     * @return CategoryEntry|null
     */
    public static function getEntryBySlug(string $slug): ?array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return null;
        }
        $manifest = self::getManifest();
        if ($manifest === null) {
            return null;
        }
        foreach ($manifest['categories'] as $row) {
            if (strtolower((string) ($row['slug'] ?? '')) === $slug) {
                return self::normalizeEntry($row);
            }
        }

        return null;
    }

    /**
     * @return CategoryEntry|null
     */
    public static function getEntryByCategoryId(int $categoryId): ?array
    {
        if ($categoryId < 1) {
            return null;
        }
        $manifest = self::getManifest();
        if ($manifest === null) {
            return null;
        }
        foreach ($manifest['categories'] as $row) {
            if ((int) ($row['category_id'] ?? 0) === $categoryId) {
                return self::normalizeEntry($row);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return CategoryEntry
     */
    public static function normalizeEntry(array $row): array
    {
        $ids = [];
        if (isset($row['video_ids']) && is_array($row['video_ids'])) {
            foreach ($row['video_ids'] as $id) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        return [
            'category_id' => (int) ($row['category_id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'label' => (string) ($row['label'] ?? ''),
            'video_ids' => array_values(array_unique($ids)),
        ];
    }

    /**
     * @return list<int> category IDs that currently feature this video in discovery
     */
    public static function categoryIdsForVideo(int $videoId): array
    {
        if ($videoId < 1) {
            return [];
        }
        $raw = Redis::get(self::REDIS_KEY_VIDEO_INDEX);
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }
        $key = (string) $videoId;
        if (! isset($decoded[$key]) || ! is_array($decoded[$key])) {
            return [];
        }
        $out = [];
        foreach ($decoded[$key] as $cid) {
            $cid = (int) $cid;
            if ($cid > 0) {
                $out[] = $cid;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<CategoryEntry>  $categories
     */
    private static function rebuildVideoCategoryIndex(array $categories): void
    {
        /** @var array<string, list<int>> $map */
        $map = [];
        foreach ($categories as $row) {
            $cid = (int) ($row['category_id'] ?? 0);
            if ($cid < 1) {
                continue;
            }
            $ids = $row['video_ids'] ?? [];
            if (! is_array($ids)) {
                continue;
            }
            foreach ($ids as $vid) {
                $vid = (int) $vid;
                if ($vid < 1) {
                    continue;
                }
                $k = (string) $vid;
                if (! isset($map[$k])) {
                    $map[$k] = [];
                }
                $map[$k][] = $cid;
            }
        }
        foreach ($map as $k => $list) {
            $map[$k] = array_values(array_unique($list));
        }
        $ttl = (int) config('vidgaze.category_discovery.ttl_seconds', 172800);
        Redis::setex(self::REDIS_KEY_VIDEO_INDEX, $ttl, json_encode($map));
    }
}
