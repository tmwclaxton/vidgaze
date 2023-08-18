<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoCollection;
use App\Http\Resources\VideoResource;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VideoApiController extends Controller
{

    protected array $allowedCategories = [
        'popular',
        'new',
        'trending',
        'recommended',
        'random',
        'awarded',
        'comments',
    ];

    protected array $allowedPlatforms = [
        'YouTube',
        'Dailymotion',
        'Vimeo',
    ];


    /**
     * Get videos with certain filters
     * @param Request $request
     * @return JsonResponse
     *
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'integer|min:1|max:50',
            // comma separated list of video ids, only allow commas and numbers
            'video_ids' => 'string|regex:/^[0-9,]+$/|nullable',
            'category' => 'string|in:' . implode(',', $this->allowedCategories),
            'platforms' => 'array|in:' . implode(',', $this->allowedPlatforms),
            'shorts' => 'boolean',
            'first_video_slug' => 'string',
        ]);

        $per_page = $request->per_page ?? 20;
        $video_ids = $request->video_ids ?? [];
        $selectedCategory = $request->category ?? 'popular';
        $shorts = $request->shorts ?? false;
        $first_video_slug = $request->first_video_slug ?? null;
        $selectedVideoPlatforms = $request->platforms ?? ['YouTube', 'Dailymotion', 'Vimeo'];


        if (!is_array($video_ids) ) {
            $video_ids = explode(',', $video_ids);
        }

        // if first_video_id is set add it to videoIds to be ignored
        if ($first_video_slug) {
            $video_ids[] = $first_video_slug;
        }

        $query = Video::query();

        switch ($selectedCategory) {
            case 'popular':
                $videoViews = VideoView::select(DB::raw('video_id, sum(duration) as total_duration, count(*) as total_views, (sum(duration) * (1 + (UNIX_TIMESTAMP(created_at) - UNIX_TIMESTAMP(NOW())) / (3600 * 24 * 7)) + count(*)) as score, created_at'))
                    ->where('created_at', '>=', Carbon::now()->subWeek())
                    ->groupBy('video_id', 'created_at')
                    ->orderBy('score', 'desc')
                    ->take(500)
                    ->get();

                // Get the most popular video IDs
                $mostPopularVideoIds = $videoViews->pluck('video_id');
                // Preserve order
                if ($mostPopularVideoIds->count() > 0) {
                    //$query->whereIn('id', $mostPopularVideoIds)->orderByRaw(DB::raw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")"));
                    $query->whereIn('id', $mostPopularVideoIds)->orderByRaw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")");

                }
                break;
            case 'new':
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                break;
            case 'trending':
                if (!isset($mostTrendingVideoIds)) {
                    $videoViewInfos = DB::table('video_interactions')
                        ->select('video_id', DB::raw('SUM(CASE WHEN liked = "like" THEN 1 ELSE 0 END) as likes'), DB::raw('SUM(CASE WHEN liked = "dislike" THEN 1 ELSE 0 END) as dislikes'))
                        ->where('created_at', '>=', Carbon::now()->subWeek())
                        ->groupBy('video_id')
                        ->limit(500)
                        ->get();

                    $mostTrendingVideoIds = $videoViewInfos->sortByDesc(function($videoId) {
                        return $videoId->likes - $videoId->dislikes;
                    })->pluck('video_id');

                }

                // Preserve order
                if ($mostTrendingVideoIds->count() > 0) {
                    //$query->whereIn('id', $mostTrendingVideoIds)->orderByRaw(DB::raw("FIELD(id, " . implode(',', $mostTrendingVideoIds->toArray()) . ")"));
                    $query->whereIn('id', $mostTrendingVideoIds)->orderByRaw("FIELD(id, " . implode(',', $mostTrendingVideoIds->toArray()) . ")");

                }
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            case 'awarded':
                $query->has('awards');
                break;
            case 'comments':
                $query->orderByDesc('comment_count');
                break;
            case 'recommended':
                //$query->where('views', '>', 0);
                return response()->json(['error' => 'Not implemented'], 400);
                break;
            default:
                return response()->json(['error' => 'Invalid category'], 400);

        }




        // Only get public videos
        $query->where('visibility', '=','public');

        if ($shorts) {
            $query->where('duration', '<', 90);
        }

        // Filter by video platform
        if (!empty($selectedVideoPlatforms)) {
            $query->whereIn('preferred_source', $selectedVideoPlatforms);
        }

        //this doesn't work for some reason
        if (Auth::user()) {
            $channelDisinterestIDs = CreatorInteraction::where('viewer_id', Auth::user()->creator->id)->where('disinterested', '=', true)
                ->pluck('creator_id')
                ->toArray();
            $query->whereNotIn('creator_id', $channelDisinterestIDs);
            $videoDisinterestIDs = VideoInteraction::where('viewer_id', Auth::user()->creator->id)->where('disinterested', '=', true)
                ->pluck('video_id')
                ->toArray();
            $query->whereNotIn('id', $videoDisinterestIDs);
        }

        // Don't retrieve the same videos
        if ( $video_ids != [] ) {
            $query->whereNotIn('id', $video_ids);
            //return ($video_ids);
        }


        $videos = $query->take($per_page)->get();


        // If there are not enough videos, get random public videos
        if (!isset($videos) || $videos->count() < $per_page) {
            // get random public videos that are not in the videoIds array and get the amt to make up the difference if there are some videos already
            if (isset($videos)) {
                $amt = $per_page - $videos->count();
            } else {
                $amt = $per_page;
            }
            $randomVideos = new VideoCollection(Video::where('visibility', 'public')->whereNotIn('id', $video_ids)->inRandomOrder()->take($amt)->get());
            if (isset($videos)) {
                $videos = $videos->merge($randomVideos);
            } else {
                $videos = $randomVideos;
            }
        }

        // if first_video_slug is not null, then find that video and put it at the beginning of the collection
        if ($first_video_slug) {
            $first_video = Video::where('slug', $first_video_slug)->first();
            if ($first_video) {
                $videos->prepend($first_video);
            }
        }

        // Retrieve the videos
        if ($query->exists()) {
            $videos = new VideoCollection($videos);
        }



        return response()->json([
            'results' => $videos->count(),
            'videos' => $videos
        ]);
    }

    public function show(string $slug) {
        $video = Video::where('slug', $slug)->firstOrFail();

        // if the stream is private and the user is not the owner
        if ($video->visibility === 'private' && $video->creator->id !== Auth::id()) {
            // return forbidden
            abort(403);
        }

        return response()->json([
            'video' => new VideoResource($video)
        ]);
    }

}
