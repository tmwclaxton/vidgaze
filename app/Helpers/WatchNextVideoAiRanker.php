<?php

namespace App\Helpers;

use App\Http\Controllers\Tools\NanoController;
use App\Models\VideoModels\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Reranks same-category watch sidebar candidates around an anchor video (cached).
 */
class WatchNextVideoAiRanker
{
    public const REDIS_KEY_PREFIX = 'watch_next:ai_rank:';

    /**
     * @param  list<Video>  $videos
     * @return array{0: list<Video>, 1: array<string, mixed>}
     */
    public static function rankVideos(Video $anchor, array $videos): array
    {
        if ($videos === []) {
            return [[], ['skipped' => true, 'reason' => 'empty_candidates']];
        }

        $apiKey = (string) config('services.nanogpt.key', '');
        $enabled = (bool) config('services.nanogpt.watch_next_ranking_enabled', true);
        $model = (string) config('services.nanogpt.watch_next_ranking_model', 'gemini-2.5-flash-lite');

        if (! $enabled || $apiKey === '') {
            return [$videos, [
                'skipped' => true,
                'reason' => $apiKey === '' ? 'nanogpt_key_missing' : 'ranking_disabled',
            ]];
        }

        $ids = collect($videos)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        $cacheKey = self::redisKey((int) $anchor->id, $ids);

        $cachedRaw = Redis::get($cacheKey);
        if (is_string($cachedRaw) && $cachedRaw !== '') {
            $cached = json_decode($cachedRaw, true);
            if (is_array($cached) && isset($cached['order']) && is_array($cached['order'])) {
                $ordered = SearchVideoAiRanker::applyOrder($videos, array_map('intval', $cached['order']));

                return [$ordered, [
                    'cached' => true,
                    'model' => $cached['model'] ?? $model,
                    'ranked_at' => $cached['ranked_at'] ?? null,
                ]];
            }
        }

        $orderedIds = self::fetchRankOrderFromNano($anchor, $videos, $model);
        if ($orderedIds === null) {
            return [$videos, [
                'skipped' => true,
                'reason' => 'nano_rank_failed',
            ]];
        }

        $ordered = SearchVideoAiRanker::applyOrder($videos, $orderedIds);

        $payload = json_encode([
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'order' => $orderedIds,
        ]);

        if ($payload !== false) {
            Redis::setex($cacheKey, Search::getRedisExpire(), $payload);
        } else {
            Log::warning('WatchNextVideoAiRanker: failed to encode rank payload');
        }

        return [$ordered, [
            'cached' => false,
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
        ]];
    }

    /**
     * @param  list<int>  $candidateIds
     */
    public static function redisKey(int $anchorVideoId, array $candidateIds): string
    {
        return self::REDIS_KEY_PREFIX.sha1((string) $anchorVideoId.'|'.implode(',', $candidateIds));
    }

    /**
     * @param  list<Video>  $videos
     * @return list<int>|null
     */
    protected static function fetchRankOrderFromNano(Video $anchor, array $videos, string $model): ?array
    {
        $catName = $anchor->category?->name ?? 'unknown';
        $anchorTitle = AiRankingResponseParser::squish((string) $anchor->title);

        $lines = [];
        foreach ($videos as $video) {
            $title = AiRankingResponseParser::squish((string) $video->title);
            $src = (string) $video->preferred_source;
            $lines[] = sprintf('%d|%s|%s', (int) $video->id, $src, $title);
        }

        $catalog = implode("\n", $lines);
        $prompt = "You are ranking related videos for the watch page sidebar.\n\n"
            ."The user is currently watching:\n"
            ."- Title: {$anchorTitle}\n"
            ."- Category: {$catName}\n\n"
            ."Rules:\n"
            ."- Output ONLY a JSON array of integers: database \"id\" values in best-to-worst order as \"watch next\" suggestions.\n"
            .'- Use every id exactly once. Do not omit or add ids.'."\n"
            ."- Strongly prefer titles that match the anchor theme; stay within the same broad topic.\n"
            ."- No markdown or explanation—only the JSON array.\n\n"
            ."Candidate videos (format: id|platform|title):\n"
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
            Log::warning('WatchNextVideoAiRanker: NanoGPT request failed', ['message' => $e->getMessage()]);

            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (! is_string($content)) {
            return null;
        }

        $allowedIds = collect($videos)->pluck('id')->map(fn ($id) => (int) $id)->all();

        return AiRankingResponseParser::parseIdArrayFromModelContent($content, $allowedIds);
    }
}
