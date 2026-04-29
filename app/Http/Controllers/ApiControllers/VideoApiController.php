<?php

namespace App\Http\Controllers\ApiControllers;

use App\Helpers\CategoryFeedBrandScores;
use App\Helpers\CategoryFeedViewerCooldown;
use App\Helpers\RecommendedVideoAiRanker;
use App\Helpers\VidgazeCategoryFeedCache;
use App\Helpers\VidgazeTrendFeedCache;
use App\Helpers\VidgazeTrendPickCache;
use App\Helpers\WatchNextVideoAiRanker;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoCollection;
use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoAward;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoApiController extends Controller
{
    protected array $allowedCategories = [
        "popular",
        "new",
        "trending",
        "recommended",
        "random",
        "awarded",
        "comments",
    ];

    protected array $allowedPlatforms = [
        "YouTube",
        "Dailymotion",
        "Vimeo",
        "Rumble",
        "Odysee",
        "BitChute",
    ];

    /**
     * Get videos with certain filters
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            "per_page" => "integer|min:1|max:50",
            // comma separated list of video ids, only allow commas and numbers
            "video_ids" => 'string|regex:/^[0-9,]+$/|nullable',
            "category" => "string|in:" . implode(",", $this->allowedCategories),
            "platforms" => "array|in:" . implode(",", $this->allowedPlatforms),
            "shorts" => "boolean",
            "first_video_slug" => "string",
            "creator_id" => "nullable|integer|exists:creators,id",
        ]);

        $per_page = $request->per_page ?? 20;
        $video_ids = $request->video_ids ?? [];
        $selectedCategory = $request->category ?? "popular";
        $shorts = $request->shorts ?? false;
        $first_video_slug = $request->first_video_slug ?? null;
        $selectedVideoPlatforms = $request->platforms ?? [
            "YouTube",
            "Dailymotion",
            "Vimeo",
        ];
        $creator_id = $request->creator_id ?? null;

        if (!is_array($video_ids)) {
            $video_ids = explode(",", $video_ids);
        }

        // if first_video_id is set add it to videoIds to be ignored
        if ($first_video_slug) {
            $first_video = Video::where("slug", $first_video_slug)->first();
            if ($first_video) {
                $first_video_id = $first_video->id;
                $video_ids[] = $first_video_id;
            }
        }

        if ($selectedCategory === "recommended") {
            return $this->indexRecommended(
                $per_page,
                $video_ids,
                $shorts,
                $first_video_slug,
                $selectedVideoPlatforms,
                $creator_id,
            );
        }

        $query = Video::query();

        switch ($selectedCategory) {
            case "popular":
                // calculate ctr by dividing views by impressions avoid division by zero by adding 1 to impressions,
                $query
                    ->selectRaw(
                        "videos.*, IF(view_count = 0, 0, ((view_count + 0.7) / (impressions_count + 1))) as ctr",
                    )
                    ->orderBy("ctr", "desc");
                break;
            case "trending":
                $videoViews = VideoView::select(
                    DB::raw(
                        "video_id, sum(duration) as total_duration, count(*) as total_views, (sum(duration) * (1 + (UNIX_TIMESTAMP(created_at) - UNIX_TIMESTAMP(NOW())) / (3600 * 24 * 7)) + count(*)) as score, created_at",
                    ),
                )
                    ->where("created_at", ">=", Carbon::now()->subWeek())
                    ->groupBy("video_id", "created_at")
                    ->orderBy("score", "desc")
                    ->take(500)
                    ->get();
                // Get the most popular video IDs
                $mostPopularVideoIds = $videoViews->pluck("video_id");
                // Preserve order
                if ($mostPopularVideoIds->count() > 0) {
                    // $query->whereIn('id', $mostPopularVideoIds)->orderByRaw(DB::raw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")"));
                    $query
                        ->whereIn("id", $mostPopularVideoIds)
                        ->orderByRaw(
                            "FIELD(id, " .
                                implode(",", $mostPopularVideoIds->toArray()) .
                                ")",
                        );
                }
                break;
            case "new":
                $query->where("created_at", ">=", Carbon::now()->subWeek());
                break;
            case "random":
                $query->inRandomOrder();
                break;
            case "awarded":
                $query->has("awards");
                break;
            case "comments":
                $query->orderByDesc("comment_count");
                break;
            default:
                return response()->json(["error" => "Invalid category"], 400);
        }
        // query where name doesn't contain Nursery or Rhymes in caps or lowercase
        $bannedWords = [
            "maravilloso",
            "animal",
            "nude",
            "naked",
            "nursery",
            "rhymes",
            "children",
            "cartoon",
            "kids",
            "finger",
            "singing",
            "toys",
            "babies",
            "family",
            "baby",
            "songs",
            "song",
            "learn",
            "learning",
            "educational",
            "shopkins",
            "shoppies",
            "numbers",
        ];
        foreach ($bannedWords as $word) {
            $query->where("title", "not like", "%" . $word . "%");
        }
        // Only get public videos
        $query->where("visibility", "=", "public");

        if ($shorts) {
            $query->where("duration", "<", 90);
        }

        // Filter by channel
        if ($creator_id) {
            $query->where("creator_id", $creator_id);
        }

        // Filter by video platform (DB stores lowercase enum values)
        if (!empty($selectedVideoPlatforms)) {
            $query->whereIn(
                "preferred_source",
                array_map("strtolower", $selectedVideoPlatforms),
            );
        }

        $query->whereNotNull("category_id");

        if (Auth::user()) {
            $channelDisinterestIDs = CreatorInteraction::where(
                "viewer_id",
                Auth::user()->creator->id,
            )
                ->where("disinterested", "=", true)
                ->pluck("creator_id")
                ->toArray();
            $query->whereNotIn("creator_id", $channelDisinterestIDs);
            $videoDisinterestIDs = VideoInteraction::where(
                "viewer_id",
                Auth::user()->creator->id,
            )
                ->where("disinterested", "=", true)
                ->pluck("video_id")
                ->toArray();
            $query->whereNotIn("id", $videoDisinterestIDs);
        }

        // Don't retrieve the same videos
        if ($video_ids != []) {
            $query->whereNotIn("id", $video_ids);
            // return ($video_ids);
        }

        $videos = $query->take($per_page)->get();

        // If there are not enough videos, get random public videos
        if (
            (!isset($videos) || $videos->count() < $per_page) &&
            $creator_id === null
        ) {
            // get random public videos that are not in the videoIds array and get the amt to make up the difference if there are some videos already
            if (isset($videos)) {
                $amt = $per_page - $videos->count();
            } else {
                $amt = $per_page;
            }
            $randomVideos = Video::where("visibility", "public")
                ->whereNotIn("id", $video_ids)
                ->inRandomOrder()
                ->take($amt)
                ->get();

            $videos = $videos->merge($randomVideos);
        }

        // if first_video_slug is not null, then find that video and put it at the beginning of the collection
        if ($first_video_slug) {
            $first_video = Video::where("slug", $first_video_slug)->first();
            if ($first_video) {
                $videos->prepend($first_video);
            }
        }

        // add 1 to impressions_count for each video in 1 query
        $video_ids = $videos->pluck("id");
        Video::whereIn("id", $video_ids)->increment("impressions_count");

        // Retrieve the videos
        $videos = new VideoCollection($videos);

        return response()->json([
            "results" => $videos->count(),
            "videos" => $videos,
        ]);
    }

    public function show(string $slug)
    {
        $video = Video::where("slug", $slug)->firstOrFail();

        // if the stream is private and the user is not the owner
        if (
            $video->visibility === "private" &&
            $video->creator->id !== Auth::id()
        ) {
            // return forbidden
            abort(403);
        }

        $video->live_viewer_count = $video->live_viewer_count + 1;
        $videoResource = new VideoResource($video);

        $videoResource = $videoResource->toJson();

        // convert the json string to an array
        $videoResource = json_decode($videoResource, true);

        // add object_awards to the videoResource
        $videoResource["object_awards"] = VideoAward::where(
            "video_id",
            $video->id,
        )->get();

        return response()->json([
            "video" => $videoResource,
        ]);
    }

    public function getPinnedVideos(Request $request)
    {
        $request->validate([
            "per_page" => "integer|min:1|max:50",
            //            'page' => 'integer|min:1',
            "category_slug" => "nullable|string|exists:categories,slug",
            "platform" =>
                "nullable|string|in:" . implode(",", $this->allowedPlatforms),
        ]);

        if ($request->category_slug) {
            $category = Category::where(
                "slug",
                $request->category_slug,
            )->first();
        } else {
            $category = null;
        }

        $per_page = $request->per_page ?? 6;
        //        $page = $request->page ?? 1;

        $query = Video::query();
        $query->where("pinned", true);

        // Apply your filters
        if ($category) {
            $query->where("category_id", $category->id);
        }

        if ($request->platform) {
            $query->where("preferred_source", $request->platform);
        }

        // Get the IDs of all matching videos
        $pinnedIds = $query->pluck("id")->toArray();

        $additionalIds = [];
        // If we need more videos
        if (count($pinnedIds) < $per_page && $category) {
            // Get additional video IDs
            $additionalIds = Video::where("category_id", $category->id)
                ->whereNotIn("id", $pinnedIds)
                ->pluck("id")
                ->toArray();
        }
        // Merge all IDs
        $allIds = array_merge($pinnedIds, $additionalIds);

        if ($category && $category->slug === "vidgaze_picks") {
            $trendPickIds = VidgazeTrendPickCache::getVideoIds();
            if ($trendPickIds !== []) {
                // Get videos with platform info and select diverse sources
                $trendVideos = Video::whereIn("id", $trendPickIds)
                    ->select("id", "preferred_source")
                    ->get();

                // Group by platform
                $platformVideos = [];
                foreach ($trendVideos as $video) {
                    $platform = $video->preferred_source ?? "youtube";
                    if (!isset($platformVideos[$platform])) {
                        $platformVideos[$platform] = [];
                    }
                    $platformVideos[$platform][] = $video->id;
                }

                // Select up to 2 videos from each platform for diversity
                $diverseTrendIds = [];
                $maxPerPlatform = 2;
                foreach ($platformVideos as $platformIdList) {
                    shuffle($platformIdList);
                    $diverseTrendIds = array_merge(
                        $diverseTrendIds,
                        array_slice($platformIdList, 0, $maxPerPlatform),
                    );
                }

                // If we still need more, add remaining videos
                if (count($diverseTrendIds) < $per_page) {
                    $remainingIds = array_diff($trendPickIds, $diverseTrendIds);
                    shuffle($remainingIds);
                    $diverseTrendIds = array_merge(
                        $diverseTrendIds,
                        array_slice(
                            $remainingIds,
                            0,
                            $per_page - count($diverseTrendIds),
                        ),
                    );
                }

                $validTrendIds = $diverseTrendIds;
                $allIds = array_merge($allIds, $validTrendIds);
            }
        }

        $allIds = array_values(array_unique($allIds));

        // For VidGaze Picks, enforce platform diversity in final selection
        if (
            $category &&
            $category->slug === "vidgaze_picks" &&
            count($allIds) > $per_page
        ) {
            // Get platform info for all videos
            $videoPlatformMap = Video::whereIn("id", $allIds)
                ->select("id", "preferred_source")
                ->get()
                ->keyBy("id")
                ->toArray();

            // Group by platform and calculate max per platform (40% max to ensure diversity)
            $platformVideos = [];
            foreach ($allIds as $id) {
                $video = $videoPlatformMap[$id] ?? null;
                $platform = $video["preferred_source"] ?? "youtube";
                if (!isset($platformVideos[$platform])) {
                    $platformVideos[$platform] = [];
                }
                $platformVideos[$platform][] = $id;
            }

            $maxPerPlatform = max(2, (int) floor($per_page * 0.4));

            // Select videos with platform diversity
            $diverseSelectedIds = [];
            foreach ($platformVideos as $platformIdList) {
                shuffle($platformIdList);
                $diverseSelectedIds = array_merge(
                    $diverseSelectedIds,
                    array_slice($platformIdList, 0, $maxPerPlatform),
                );
            }

            // If we still need more, add remaining videos from all platforms
            if (count($diverseSelectedIds) < $per_page) {
                $remainingIds = array_diff($allIds, $diverseSelectedIds);
                shuffle($remainingIds);
                $diverseSelectedIds = array_merge(
                    $diverseSelectedIds,
                    array_slice(
                        $remainingIds,
                        0,
                        $per_page - count($diverseSelectedIds),
                    ),
                );
            }

            $selectedIds = array_values($diverseSelectedIds);
        } else {
            // Shuffle all IDs for random order
            shuffle($allIds);

            // Take only what we need
            $selectedIds = array_slice($allIds, 0, $per_page);
        }

        if ($selectedIds === []) {
            $videos = new VideoCollection(collect());

            return response()->json([
                "results" => 0,
                "videos" => $videos,
            ]);
        }

        // Get the videos in random order (just by id, not FIELD order)
        $videos = Video::whereIn("id", $selectedIds)->get();

        // return the collection
        $videos = new VideoCollection($videos);

        return response()->json([
            "results" => $videos->count(),
            "videos" => $videos,
        ]);
    }

    public function getVideosByCategory(Request $request)
    {
        $request->validate([
            "per_page" => "integer|min:1|max:50",
            "video_ids" => "string|nullable",
            "slug" => "string|exists:categories,slug",
            "anchor_video_id" => "nullable|integer|exists:videos,id",
        ]);

        $per_page = $request->per_page ?? 20;
        $video_ids = $request->video_ids ?? [];
        $category_slug = $request->slug;
        $anchorVideoId = $request->filled("anchor_video_id")
            ? (int) $request->input("anchor_video_id")
            : null;

        if (!is_array($video_ids)) {
            $video_ids = explode(",", $video_ids);
        }

        $category = Category::where("slug", $category_slug)->first();

        $poolMult = max(
            2,
            (int) config(
                "vidgaze.recommendations.watch_next_brand_pool_multiplier",
                2,
            ),
        );
        $fetchCount = min(50, $per_page * $poolMult);

        $query = Video::query();

        $query->where("category_id", $category->id);

        if ($video_ids != []) {
            $query->whereNotIn("id", $video_ids);
        }

        $query->where("visibility", "public");

        $candidateIds = (clone $query)
            ->orderByDesc("time_published")
            ->limit($fetchCount)
            ->pluck("id")
            ->all();
        if ($candidateIds === []) {
            return response()->json([
                "results" => 0,
                "videos" => new VideoCollection(collect()),
            ]);
        }

        $scores = CategoryFeedBrandScores::getScores(
            (int) $category->id,
            $candidateIds,
        );
        arsort($scores);
        $sortedIds = array_keys($scores);

        $anchor = null;
        if ($anchorVideoId !== null) {
            $anchor = Video::query()
                ->whereKey($anchorVideoId)
                ->with("category")
                ->first();
            if (
                $anchor !== null &&
                (int) $anchor->category_id !== (int) $category->id
            ) {
                $anchor = null;
            }
        }

        $aiCap = min(
            48,
            max(
                $per_page,
                (int) config(
                    "vidgaze.recommendations.watch_next_ai_candidate_cap",
                    48,
                ),
            ),
        );
        if (
            $anchor !== null &&
            (bool) config(
                "services.nanogpt.watch_next_ranking_enabled",
                true,
            ) &&
            count($sortedIds) > $per_page
        ) {
            $sortedIds = array_slice($sortedIds, 0, $aiCap);
        }

        $pickIds = array_slice($sortedIds, 0, $fetchCount);
        $orderIndex = array_flip(array_map("intval", $pickIds));
        $videos = Video::query()
            ->whereIn("id", $pickIds)
            ->get()
            ->sortBy(fn(Video $v) => $orderIndex[(int) $v->id] ?? 1_000_000)
            ->values();

        if ($anchor !== null && $videos->count() > $per_page) {
            $pool = $videos->all();
            [$ranked, $_meta] = WatchNextVideoAiRanker::rankVideos(
                $anchor,
                $pool,
            );
            $videos = collect($ranked)->take($per_page)->values();
        } else {
            $videos = $videos->take($per_page)->values();
        }

        return response()->json([
            "results" => $videos->count(),
            "videos" => new VideoCollection($videos),
        ]);
    }

    public function trendFeedTopics(): JsonResponse
    {
        $manifest = VidgazeTrendFeedCache::getManifest();
        if ($manifest === null) {
            return response()->json([
                "updated_at" => null,
                "topics" => [],
            ]);
        }

        $topics = [];
        foreach ($manifest["topics"] as $row) {
            $key = $row["key"] ?? "";
            $label = $row["label"] ?? "";
            $ids = $row["video_ids"] ?? [];
            if ($key === "" || $label === "") {
                continue;
            }
            $topics[] = [
                "key" => $key,
                "label" => $label,
                "count" => is_array($ids) ? count($ids) : 0,
            ];
        }

        return response()->json([
            "updated_at" => $manifest["updated_at"] ?? null,
            "topics" => $topics,
        ]);
    }

    public function trendFeedVideos(Request $request): JsonResponse
    {
        $request->validate([
            "key" => ["required", "string", 'regex:/^[a-f0-9]{16}$/'],
        ]);

        $ids = VidgazeTrendFeedCache::getVideoIdsForKey(
            $request->string("key")->toString(),
        );
        if ($ids === []) {
            return response()->json([
                "results" => 0,
                "videos" => new VideoCollection(collect()),
            ]);
        }

        $videos = Video::whereIn("id", $ids)
            ->orderByRaw(
                "FIELD(id, " . implode(",", array_map("intval", $ids)) . ")",
            )
            ->get();

        return response()->json([
            "results" => $videos->count(),
            "videos" => new VideoCollection($videos),
        ]);
    }

    public function categoryFeedSlots(): JsonResponse
    {
        $manifest = VidgazeCategoryFeedCache::getManifest();
        if ($manifest === null) {
            return response()->json([
                "updated_at" => null,
                "slots" => [],
            ]);
        }

        $slots = [];
        foreach ($manifest["categories"] as $row) {
            $entry = VidgazeCategoryFeedCache::normalizeEntry($row);
            if ($entry["category_id"] < 1 || $entry["slug"] === "") {
                continue;
            }
            $slots[] = [
                "category_id" => $entry["category_id"],
                "slug" => $entry["slug"],
                "name" => $entry["name"],
                "label" => $entry["label"],
                "count" => count($entry["video_ids"]),
            ];
        }

        return response()->json([
            "updated_at" => $manifest["updated_at"] ?? null,
            "slots" => $slots,
        ]);
    }

    public function categoryFeedVideos(Request $request): JsonResponse
    {
        $request->validate([
            "category_id" => [
                "nullable",
                "integer",
                "min:1",
                "required_without:category_slug",
            ],
            "category_slug" => [
                "nullable",
                "string",
                "max:64",
                'regex:/^[a-z0-9_-]+$/',
                "required_without:category_id",
            ],
            "feed_client" => ["nullable", "uuid"],
            "limit" => ["nullable", "integer", "min:1", "max:48"],
        ]);

        $limit =
            (int) ($request->input("limit") ??
                config("vidgaze.category_discovery.api_max_videos", 24));
        $limit = max(1, min(48, $limit));

        $entry = null;
        if ($request->filled("category_id")) {
            $entry = VidgazeCategoryFeedCache::getEntryByCategoryId(
                (int) $request->input("category_id"),
            );
        }
        if ($entry === null && $request->filled("category_slug")) {
            $entry = VidgazeCategoryFeedCache::getEntryBySlug(
                (string) $request->input("category_slug"),
            );
        }

        if ($entry === null || $entry["video_ids"] === []) {
            return response()->json([
                "results" => 0,
                "label" => null,
                "category_id" => null,
                "slug" => null,
                "videos" => new VideoCollection(collect()),
            ]);
        }

        $viewerId = Auth::user()?->creator?->id;
        $feedClient = $request->input("feed_client");
        if (is_string($feedClient) && Str::isUuid($feedClient)) {
            $feedClient = (string) $feedClient;
        } else {
            $feedClient = null;
        }

        $ids = $entry["video_ids"];
        $scores = CategoryFeedBrandScores::getScores(
            $entry["category_id"],
            $ids,
        );
        arsort($scores);
        $sortedIds = array_keys($scores);

        $recent = CategoryFeedViewerCooldown::getRecent(
            is_int($viewerId) ? $viewerId : null,
            $feedClient,
            $entry["category_id"],
        );
        $recentSet = array_fill_keys($recent, true);
        $filtered = [];
        foreach ($sortedIds as $id) {
            if (!isset($recentSet[$id])) {
                $filtered[] = $id;
            }
        }

        $pick = array_slice($filtered, 0, $limit);
        if (count($pick) < min(6, count($sortedIds)) && count($sortedIds) > 0) {
            foreach ($sortedIds as $id) {
                if (count($pick) >= $limit) {
                    break;
                }
                if (!in_array($id, $pick, true)) {
                    $pick[] = $id;
                }
            }
        }

        CategoryFeedViewerCooldown::pushRecent(
            is_int($viewerId) ? $viewerId : null,
            $feedClient,
            $entry["category_id"],
            $pick,
        );

        $videos = Video::whereIn("id", $pick)
            ->orderByRaw(
                "FIELD(id, " . implode(",", array_map("intval", $pick)) . ")",
            )
            ->get();

        return response()->json([
            "results" => $videos->count(),
            "label" => $entry["label"],
            "category_id" => $entry["category_id"],
            "slug" => $entry["slug"],
            "videos" => new VideoCollection($videos),
        ]);
    }

    private function indexRecommended(
        int $per_page,
        array $video_ids,
        bool $shorts,
        ?string $first_video_slug,
        array $selectedVideoPlatforms,
        ?int $creator_id,
    ): JsonResponse {
        $excludeIds = array_values(
            array_unique(
                array_filter(
                    array_map("intval", $video_ids),
                    fn(int $id) => $id > 0,
                ),
            ),
        );

        $viewerCreatorId = Auth::user()?->creator?->id;
        $subscribedCreatorIds = [];
        $interestCategoryIds = [];

        if ($viewerCreatorId !== null) {
            $subscribedCreatorIds = Auth::user()
                ->creator->subscriptions->pluck("id")
                ->all();

            $interestCategoryIds = VideoView::query()
                ->where("video_views.viewer_id", $viewerCreatorId)
                ->where(
                    "video_views.created_at",
                    ">=",
                    Carbon::now()->subDays(
                        (int) config(
                            "vidgaze.recommendations.history_days",
                            90,
                        ),
                    ),
                )
                ->join("videos", "video_views.video_id", "=", "videos.id")
                ->whereNotNull("videos.category_id")
                ->selectRaw("videos.category_id, COUNT(*) as c")
                ->groupBy("videos.category_id")
                ->orderByDesc("c")
                ->limit(
                    (int) config(
                        "vidgaze.recommendations.max_interest_categories",
                        5,
                    ),
                )
                ->pluck("videos.category_id")
                ->all();
        }

        $cap = (int) config("vidgaze.recommendations.candidate_cap", 64);
        $mergedIds = [];

        $subLimit = (int) config(
            "vidgaze.recommendations.subscribed_pool_limit",
            32,
        );
        if ($subscribedCreatorIds !== [] && $creator_id === null) {
            $q = $this->recommendedCandidateQuery(
                $excludeIds,
                $shorts,
                $selectedVideoPlatforms,
                $creator_id,
            );
            $q->whereIn("creator_id", $subscribedCreatorIds)
                ->orderByDesc("time_published")
                ->limit($subLimit);
            $mergedIds = array_merge($mergedIds, $q->pluck("id")->all());
        }

        $catLimit = (int) config(
            "vidgaze.recommendations.category_pool_limit",
            32,
        );
        if ($interestCategoryIds !== [] && $creator_id === null) {
            $q = $this->recommendedCandidateQuery(
                $excludeIds,
                $shorts,
                $selectedVideoPlatforms,
                $creator_id,
            );
            $q->whereIn("category_id", $interestCategoryIds)
                ->orderByDesc("time_published")
                ->limit($catLimit);
            $mergedIds = array_merge($mergedIds, $q->pluck("id")->all());
        }

        $mergedIds = array_values(
            array_unique(array_map("intval", $mergedIds)),
        );

        if (count($mergedIds) < $cap && $creator_id === null) {
            $q = $this->recommendedCandidateQuery(
                $excludeIds,
                $shorts,
                $selectedVideoPlatforms,
                $creator_id,
            );
            $q->selectRaw(
                "videos.*, IF(view_count = 0, 0, ((view_count + 0.7) / (impressions_count + 1))) as ctr",
            )
                ->orderBy("ctr", "desc")
                ->whereNotIn("id", array_merge($excludeIds, $mergedIds))
                ->limit(max(1, $cap - count($mergedIds)));
            $mergedIds = array_merge($mergedIds, $q->pluck("id")->all());
        }

        $mergedIds = array_slice(
            array_values(array_unique(array_map("intval", $mergedIds))),
            0,
            $cap,
        );

        $videosArr =
            $mergedIds === []
                ? []
                : Video::query()->whereIn("id", $mergedIds)->get()->all();

        $videosArr = RecommendedVideoAiRanker::sortHeuristic(
            $videosArr,
            $subscribedCreatorIds,
            $interestCategoryIds,
        );
        $profile = RecommendedVideoAiRanker::buildViewerProfile(
            $viewerCreatorId,
            $subscribedCreatorIds,
            $interestCategoryIds,
        );
        [$ranked, $_meta] = RecommendedVideoAiRanker::rankVideos(
            $videosArr,
            $profile,
            $viewerCreatorId,
        );
        $videos = collect($ranked);

        if ($videos->count() < $per_page && $creator_id === null) {
            $amt = $per_page - $videos->count();
            $fillExclude = array_values(
                array_unique(
                    array_merge($excludeIds, $videos->pluck("id")->all()),
                ),
            );
            $randomVideos = $this->recommendedCandidateQuery(
                $fillExclude,
                $shorts,
                $selectedVideoPlatforms,
                null,
            )
                ->inRandomOrder()
                ->take($amt)
                ->get();
            $videos = $videos->merge($randomVideos);
        }

        $videos = $videos->take($per_page)->values();

        if ($first_video_slug) {
            $first_video = Video::where("slug", $first_video_slug)->first();
            if ($first_video) {
                $videos = $videos
                    ->filter(fn($v) => (int) $v->id !== (int) $first_video->id)
                    ->values();
                $videos->prepend($first_video);
            }
        }

        $outIds = $videos->pluck("id");
        if ($outIds->isNotEmpty()) {
            Video::whereIn("id", $outIds)->increment("impressions_count");
        }

        return response()->json([
            "results" => $videos->count(),
            "videos" => new VideoCollection($videos),
        ]);
    }

    /**
     * @return list<string>
     */
    private function bannedTitleWords(): array
    {
        return [
            "maravilloso",
            "animal",
            "nude",
            "naked",
            "nursery",
            "rhymes",
            "children",
            "cartoon",
            "kids",
            "finger",
            "singing",
            "toys",
            "babies",
            "family",
            "baby",
            "songs",
            "song",
            "learn",
            "learning",
            "educational",
            "shopkins",
            "shoppies",
            "numbers",
        ];
    }

    private function recommendedCandidateQuery(
        array $excludeIds,
        bool $shorts,
        array $selectedVideoPlatforms,
        ?int $creator_id,
    ): Builder {
        $query = Video::query()
            ->where("visibility", "=", "public")
            ->whereNotNull("category_id");

        foreach ($this->bannedTitleWords() as $word) {
            $query->where("title", "not like", "%" . $word . "%");
        }

        if ($shorts) {
            $query->where("duration", "<", 90);
        }

        if ($creator_id) {
            $query->where("creator_id", $creator_id);
        }

        if (!empty($selectedVideoPlatforms)) {
            $query->whereIn(
                "preferred_source",
                array_map("strtolower", $selectedVideoPlatforms),
            );
        }

        if (Auth::user()) {
            $channelDisinterestIDs = CreatorInteraction::where(
                "viewer_id",
                Auth::user()->creator->id,
            )
                ->where("disinterested", "=", true)
                ->pluck("creator_id")
                ->toArray();
            $query->whereNotIn("creator_id", $channelDisinterestIDs);
            $videoDisinterestIDs = VideoInteraction::where(
                "viewer_id",
                Auth::user()->creator->id,
            )
                ->where("disinterested", "=", true)
                ->pluck("video_id")
                ->toArray();
            $query->whereNotIn("id", $videoDisinterestIDs);
        }

        if ($excludeIds !== []) {
            $query->whereNotIn("id", $excludeIds);
        }

        return $query;
    }
}
