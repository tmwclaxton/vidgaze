<?php

namespace App\Helpers;

use App\Http\Controllers\Tools\NanoController;
use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Ranks personalised "For you" candidates via NanoGPT (Gemini) with Redis cache.
 */
class RecommendedVideoAiRanker
{
    public const REDIS_KEY_PREFIX = 'recommended:ai_rank:';

    /**
     * @param  list<int>  $subscribedCreatorIds
     * @param  list<int>  $interestCategoryIds
     */
    public static function buildViewerProfile(
        ?int $viewerCreatorId,
        array $subscribedCreatorIds,
        array $interestCategoryIds
    ): string {
        if ($viewerCreatorId === null) {
            return 'Anonymous viewer. No channel subscriptions or watch history available.';
        }

        $lines = ['Signed-in viewer (channel profile for recommendations).'];

        $subs = Creator::query()
            ->whereIn('id', array_values(array_unique(array_filter($subscribedCreatorIds, fn ($id) => (int) $id > 0))))
            ->orderBy('name')
            ->limit(20)
            ->pluck('name')
            ->all();

        if ($subs !== []) {
            $lines[] = 'Subscribed channels (names): '.implode(', ', $subs).'.';
        } else {
            $lines[] = 'No channel subscriptions on record.';
        }

        if ($interestCategoryIds !== []) {
            $cats = Category::query()
                ->whereIn('id', array_values(array_unique(array_filter($interestCategoryIds, fn ($id) => (int) $id > 0))))
                ->orderBy('name')
                ->pluck('name')
                ->all();
            if ($cats !== []) {
                $lines[] = 'Categories inferred from recent watches: '.implode(', ', $cats).'.';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Heuristic ordering when AI is skipped or fails.
     *
     * @param  list<Video>  $videos
     * @param  list<int>  $subscribedCreatorIds
     * @param  list<int>  $interestCategoryIds
     * @return list<Video>
     */
    public static function sortHeuristic(array $videos, array $subscribedCreatorIds, array $interestCategoryIds): array
    {
        if ($videos === []) {
            return [];
        }

        $subSet = array_fill_keys(array_map('intval', $subscribedCreatorIds), true);
        $catSet = array_fill_keys(array_map('intval', $interestCategoryIds), true);

        usort($videos, function (Video $a, Video $b) use ($subSet, $catSet) {
            $scoreA = self::heuristicScore($a, $subSet, $catSet);
            $scoreB = self::heuristicScore($b, $subSet, $catSet);

            return $scoreB <=> $scoreA;
        });

        return $videos;
    }

    /**
     * @param  array<int, true>  $subSet
     * @param  array<int, true>  $catSet
     */
    protected static function heuristicScore(Video $video, array $subSet, array $catSet): float
    {
        $impressions = max(1, (int) $video->impressions_count);
        $ctr = ((int) $video->view_count + 0.7) / ($impressions + 1);
        $subBoost = isset($subSet[(int) $video->creator_id]) ? 1.0 : 0.0;
        $catBoost = $video->category_id !== null && isset($catSet[(int) $video->category_id]) ? 0.5 : 0.0;
        $published = $video->time_published?->timestamp ?? $video->created_at?->timestamp ?? 0;
        $recency = $published > 0 ? log(1 + max(0, $published - (time() - 86400 * 365))) : 0.0;

        return $subBoost * 4.0 + $catBoost * 2.0 + $ctr * 3.0 + $recency * 0.02;
    }

    /**
     * @param  list<Video>  $videos
     * @return array{0: list<Video>, 1: array<string, mixed>}
     */
    public static function rankVideos(array $videos, string $viewerProfile, ?int $viewerCreatorId): array
    {
        if ($videos === []) {
            return [[], ['skipped' => true, 'reason' => 'empty_candidates']];
        }

        $apiKey = (string) config('services.nanogpt.key', '');
        $enabled = (bool) config('services.nanogpt.recommended_ranking_enabled', true);
        $model = (string) config('services.nanogpt.recommended_ranking_model', 'gemini-2.5-flash-lite');

        if ($viewerCreatorId === null || ! $enabled || $apiKey === '') {
            return [$videos, [
                'skipped' => true,
                'reason' => $viewerCreatorId === null ? 'guest_viewer' : ($apiKey === '' ? 'nanogpt_key_missing' : 'ranking_disabled'),
            ]];
        }

        $ids = collect($videos)->pluck('id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
        $cacheKey = self::redisKey($viewerCreatorId, $ids);

        $cachedRaw = Redis::get($cacheKey);
        if (is_string($cachedRaw) && $cachedRaw !== '') {
            $cached = json_decode($cachedRaw, true);
            if (is_array($cached) && isset($cached['order']) && is_array($cached['order'])) {
                $ordered = SearchVideoAiRanker::applyOrder($videos, array_map('intval', $cached['order']));

                return [$ordered, [
                    'cached' => true,
                    'model' => $cached['model'] ?? $model,
                    'ranked_at' => $cached['ranked_at'] ?? null,
                    'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
                ]];
            }
        }

        $orderedIds = self::fetchRankOrderFromNano($viewerProfile, $videos, $model);
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
            Log::warning('RecommendedVideoAiRanker: failed to encode rank payload');
        }

        return [$ordered, [
            'cached' => false,
            'model' => $model,
            'ranked_at' => now()->toIso8601String(),
            'redis_key_suffix' => substr($cacheKey, strlen(self::REDIS_KEY_PREFIX)),
        ]];
    }

    /**
     * @param  list<int>  $videoDbIds
     */
    public static function redisKey(int $viewerCreatorId, array $videoDbIds): string
    {
        $fingerprint = implode(',', $videoDbIds);

        return self::REDIS_KEY_PREFIX.sha1((string) $viewerCreatorId.'|'.$fingerprint);
    }

    /**
     * @param  list<Video>  $videos
     * @return list<int>|null
     */
    protected static function fetchRankOrderFromNano(string $viewerProfile, array $videos, string $model): ?array
    {
        $lines = [];
        foreach ($videos as $video) {
            $title = AiRankingResponseParser::squish((string) $video->title);
            $src = (string) $video->preferred_source;
            $lines[] = sprintf('%d|%s|%s', (int) $video->id, $src, $title);
        }

        $catalog = implode("\n", $lines);
        $prompt = "You are ranking videos for a personalised \"For you\" feed.\n\n"
            ."Rules:\n"
            ."- Output ONLY a JSON array of integers: the database \"id\" values in best-to-worst order for this viewer.\n"
            .'- Use every id exactly once. Do not omit or add ids.'."\n"
            ."- Prefer diversity of channels when scores are similar.\n"
            ."- No markdown, no explanation—only the JSON array.\n\n"
            ."Viewer context:\n".trim($viewerProfile)."\n\n"
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
                ['max_tokens' => 4096, 'temperature' => 0.25],
                false
            );
        } catch (Throwable $e) {
            Log::warning('RecommendedVideoAiRanker: NanoGPT request failed', ['message' => $e->getMessage()]);

            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? null;
        if (! is_string($content)) {
            Log::warning('RecommendedVideoAiRanker: empty NanoGPT content', ['response_keys' => array_keys($response ?? [])]);

            return null;
        }

        $allowedIds = collect($videos)->pluck('id')->map(fn ($id) => (int) $id)->all();

        return AiRankingResponseParser::parseIdArrayFromModelContent($content, $allowedIds);
    }
}
