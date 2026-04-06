<?php

namespace App\Helpers;

use App\Http\Controllers\Tools\NanoController;
use App\Models\VideoModels\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Ranks search result videos by relevancy via NanoGPT (Gemini) and caches ordering in Redis.
 */
class SearchVideoAiRanker
{
    public const REDIS_KEY_PREFIX = 'search:ai_video_rank:';

    /**
     * Stable Redis key: query text + fingerprint of the current result set (sorted DB ids).
     *
     * @param  list<int>  $videoDbIds
     */
    public static function redisKey(string $searchQuery, array $videoDbIds): string
    {
        $normalized = mb_strtolower(trim($searchQuery));
        $fingerprint = implode(',', $videoDbIds);

        return self::REDIS_KEY_PREFIX.sha1($normalized.'|'.$fingerprint);
    }

    /**
     * @param  list<Video>  $videos
     * @return array{0: list<Video>, 1: array<string, mixed>|null} Ordered videos and metadata for the API (null if ranking skipped)
     */
    public static function rankVideos(array $videos, string $searchQuery): array
    {
        if ($videos === []) {
            return [[], null];
        }

        $apiKey = (string) config('services.nanogpt.key', '');
        $enabled = (bool) config('services.nanogpt.search_ranking_enabled', true);
        $model = (string) config('services.nanogpt.search_ranking_model', 'gemini-2.0-flash-lite');

        if (! $enabled || $apiKey === '') {
            return [$videos, [
                'skipped' => true,
                'reason' => $apiKey === '' ? 'nanogpt_key_missing' : 'ranking_disabled',
            ]];
        }

        $ids = collect($videos)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        $cacheKey = self::redisKey($searchQuery, $ids);

        $cachedRaw = Redis::get($cacheKey);
        if (is_string($cachedRaw) && $cachedRaw !== '') {
            $cached = json_decode($cachedRaw, true);
            if (is_array($cached) && isset($cached['order']) && is_array($cached['order'])) {
                $ordered = self::applyOrder($videos, array_map('intval', $cached['order']));

                return [$ordered, [
                    'cached' => true,
                    'model' => $cached['model'] ?? $model,
                    'ranked_at' => $cached['ranked_at'] ?? null,
                    'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
                ]];
            }
        }

        $orderedIds = self::fetchRankOrderFromNano($searchQuery, $videos, $model);
        if ($orderedIds === null) {
            return [$videos, [
                'skipped' => true,
                'reason' => 'nano_rank_failed',
            ]];
        }

        $ordered = self::applyOrder($videos, $orderedIds);

        $payload = json_encode([
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'order' => $orderedIds,
        ], JSON_THROW_ON_ERROR);

        Redis::setex($cacheKey, Search::getRedisExpire(), $payload);

        return [$ordered, [
            'cached' => false,
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
        ]];
    }

    /**
     * @param  list<Video>  $videos
     * @param  list<int>  $orderedIds
     * @return list<Video>
     */
    public static function applyOrder(array $videos, array $orderedIds): array
    {
        $byId = collect($videos)->keyBy(fn (Video $v) => (int) $v->id);
        $out = [];
        $seen = [];

        foreach ($orderedIds as $id) {
            $id = (int) $id;
            if (isset($seen[$id])) {
                continue;
            }
            if ($byId->has($id)) {
                $out[] = $byId->get($id);
                $seen[$id] = true;
            }
        }

        foreach ($videos as $video) {
            $id = (int) $video->id;
            if (! isset($seen[$id])) {
                $out[] = $video;
                $seen[$id] = true;
            }
        }

        return $out;
    }

    /**
     * @param  list<Video>  $videos
     * @return list<int>|null
     */
    protected static function fetchRankOrderFromNano(string $searchQuery, array $videos, string $model): ?array
    {
        $lines = [];
        foreach ($videos as $i => $video) {
            $title = self::squish((string) $video->title);
            $src = (string) $video->preferred_source;
            $lines[] = sprintf('%d|%s|%s', (int) $video->id, $src, $title);
        }

        $catalog = implode("\n", $lines);
        $prompt = "You are ranking video search results by relevance to the user's query.\n\n"
            ."Rules:\n"
            ."- Output ONLY a JSON array of integers: the database \"id\" values in best-to-worst relevance order.\n"
            .'- Use every id exactly once. Do not omit or add ids.'."\n"
            ."- No markdown, no explanation, no keys—only the JSON array.\n\n"
            ."User query:\n".trim($searchQuery)."\n\n"
            ."Videos (format: id|platform|title):\n"
            .$catalog;

        try {
            $nano = new NanoController;
            $response = $nano->getChatCompletion(
                [
                    ['role' => 'system', 'content' => 'You return only valid JSON arrays of integers.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                $model,
                ['max_tokens' => 4096, 'temperature' => 0.2],
                false
            );
        } catch (Throwable $e) {
            Log::warning('SearchVideoAiRanker: NanoGPT request failed', ['message' => $e->getMessage()]);

            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (! is_string($content)) {
            Log::warning('SearchVideoAiRanker: empty NanoGPT content', ['response_keys' => array_keys($response ?? [])]);

            return null;
        }

        $ids = self::parseIdArrayFromModelContent($content, collect($videos)->pluck('id')->map(fn ($id) => (int) $id)->all());
        if ($ids === null) {
            return null;
        }

        return $ids;
    }

    /**
     * @param  list<int>  $allowedIds
     * @return list<int>|null
     */
    protected static function parseIdArrayFromModelContent(string $content, array $allowedIds): ?array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/', '', $content) ?? $content;

        $allowedSet = array_fill_keys($allowedIds, true);

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            if (preg_match('/\[\s*[\d\s,]+\s*\]/', $content, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        if (! is_array($decoded)) {
            Log::warning('SearchVideoAiRanker: could not parse JSON array from model', ['snippet' => substr($content, 0, 200)]);

            return null;
        }

        $out = [];
        foreach ($decoded as $item) {
            if (is_int($item) || is_string($item) && ctype_digit($item)) {
                $id = (int) $item;
                if (isset($allowedSet[$id]) && ! in_array($id, $out, true)) {
                    $out[] = $id;
                }
            }
        }

        foreach ($allowedIds as $id) {
            if (! in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        if (count($out) !== count($allowedIds)) {
            return null;
        }

        return $out;
    }

    protected static function squish(string $s): string
    {
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return trim(mb_substr($s, 0, 200));
    }
}
