<?php

namespace App\Helpers;

use App\Http\Controllers\Tools\NanoController;
use App\Jobs\RankSearchVideosJob;
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
        $rankAsync = (bool) config('services.nanogpt.search_ranking_async', true);
        $model = (string) config('services.nanogpt.search_ranking_model', 'gemini-2.5-flash-lite');

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

        $videoStubs = self::videoStubsFromModels($videos);

        if ($rankAsync) {
            RankSearchVideosJob::dispatch($searchQuery, $ids, $videoStubs, $model)
                ->onQueue('search');

            return [$videos, [
                'pending' => true,
                'reason' => 'ranking_queued',
                'model' => $model,
                'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
            ]];
        }

        $orderedIds = self::fetchRankOrderFromNano($searchQuery, $videos, $model);
        if ($orderedIds === null) {
            return [$videos, [
                'skipped' => true,
                'reason' => 'nano_rank_failed',
            ]];
        }

        $ordered = self::applyOrder($videos, $orderedIds);
        $meta = self::persistRankOrder($cacheKey, $model, $orderedIds);

        return [$ordered, $meta];
    }

    /**
     * @param  list<array{id:int, title:string, preferred_source:string}>  $videoStubs
     */
    public static function computeAndCacheRankFromStubs(
        string $searchQuery,
        array $sortedVideoIds,
        array $videoStubs,
        string $model
    ): void {
        $cacheKey = self::redisKey($searchQuery, $sortedVideoIds);
        $existing = Redis::get($cacheKey);
        if (is_string($existing) && $existing !== '') {
            return;
        }

        $orderedIds = self::fetchRankOrderFromStubs($searchQuery, $videoStubs, $model);
        if ($orderedIds === null) {
            return;
        }

        self::persistRankOrder($cacheKey, $model, $orderedIds);
    }

    /**
     * @param  list<Video>  $videos
     * @return list<array{id:int, title:string, preferred_source:string}>
     */
    public static function videoStubsFromModels(array $videos): array
    {
        $stubs = [];
        foreach ($videos as $video) {
            $stubs[] = [
                'id' => (int) $video->id,
                'title' => AiRankingResponseParser::squish((string) $video->title),
                'preferred_source' => (string) $video->preferred_source,
            ];
        }

        return $stubs;
    }

    /**
     * @param  list<int>  $orderedIds
     * @return array<string, mixed>
     */
    protected static function persistRankOrder(string $cacheKey, string $model, array $orderedIds): array
    {
        $payload = json_encode([
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'order' => $orderedIds,
        ]);

        if ($payload === false) {
            Log::warning('SearchVideoAiRanker: failed to encode rank payload');

            return [
                'cached' => false,
                'model' => $model,
                'ranked_at' => now()->toIso8601String(),
                'redis_persist_failed' => true,
            ];
        }

        Redis::setex($cacheKey, Search::getRedisExpire(), $payload);

        return [
            'cached' => false,
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
        ];
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
     */
    protected static function fetchRankOrderFromNano(string $searchQuery, array $videos, string $model): ?array
    {
        return self::fetchRankOrderFromStubs($searchQuery, self::videoStubsFromModels($videos), $model);
    }

    /**
     * @param  list<array{id:int, title:string, preferred_source:string}>  $stubs
     */
    protected static function fetchRankOrderFromStubs(string $searchQuery, array $stubs, string $model): ?array
    {
        if ($stubs === []) {
            return [];
        }

        $lines = [];
        foreach ($stubs as $stub) {
            $lines[] = sprintf('%d|%s|%s', (int) $stub['id'], $stub['preferred_source'], $stub['title']);
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

        $allowedIds = array_map(fn ($s) => (int) $s['id'], $stubs);
        $ids = AiRankingResponseParser::parseIdArrayFromModelContent($content, $allowedIds);
        if ($ids === null) {
            return null;
        }

        return $ids;
    }
}
