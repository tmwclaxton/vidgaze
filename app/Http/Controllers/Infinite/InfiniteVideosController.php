<?php
namespace App\Http\Controllers\Infinite;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoViews;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InfiniteVideosController extends Controller
{
    //create route for api calls, homeinfinitescrollcontroller, that accepts
    // an array of video ids and a category
    public function get(Request $request)
    {

        $perPage = 20;
        //get ids from params
        $videoIds = $request->ids ?? [];

        if (!is_array($videoIds) ) {
            //explode the ids into an array
            $videoIds = explode(',', $videoIds);
        }


        // Get the selected category
        $selectedCategory = $request->input('category') ?? 'popular';



        $query = Video::query();

        $selectedVideoPlatforms = ['YouTube', 'Dailymotion', 'Vimeo'];

        if ($selectedCategory == 'popular') {

            $videoViews = VideoViews::select(DB::raw('video_id, sum(duration) as total_duration, count(*) as total_views, (sum(duration) * (1 + (UNIX_TIMESTAMP(created_at) - UNIX_TIMESTAMP(NOW())) / (3600 * 24 * 7)) + count(*)) as score, created_at'))
                ->where('created_at', '>=', Carbon::now()->subWeek())
                ->groupBy('video_id', 'created_at')
                ->orderBy('score', 'desc')
                ->take(500)
                ->get();

            // Get the most popular video IDs
            $mostPopularVideoIds = $videoViews->pluck('video_id');
            // Preserve order
            if ($mostPopularVideoIds->count() > 0) {
                $query->whereIn('id', $mostPopularVideoIds)->orderByRaw(DB::raw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")"));
            }

        } elseif ($selectedCategory == 'trending') {
            if (!isset($mostTrendingVideoIds)) {
                $videoViewInfos = DB::table('video_view_infos')
                    ->select('video_id', DB::raw('SUM(CASE WHEN liked = "like" THEN 1 ELSE 0 END) as likes'), DB::raw('SUM(CASE WHEN liked = "dislike" THEN 1 ELSE 0 END) as dislikes'))
                    ->where('created_at', '>=', Carbon::now()->subWeek())
                    ->groupBy('video_id')
                    ->orderByRaw('likes - dislikes DESC')
                    ->limit(500)
                    ->get();

                $mostTrendingVideoIds = $videoViewInfos->sortByDesc(function($videoId) {
                    return $videoId->likes - $videoId->dislikes;
                })->pluck('video_id');

            }

            // Preserve order
            if ($mostTrendingVideoIds->count() > 0) {
                $query->whereIn('id', $mostTrendingVideoIds)->orderByRaw(DB::raw("FIELD(id, " . implode(',', $mostTrendingVideoIds->toArray()) . ")"));
            }

        } elseif ($selectedCategory == 'new') {
            $query->orderByDesc('time_published');
        } elseif ($selectedCategory == 'random') {
            $query->inRandomOrder();
        } elseif ($selectedCategory == 'awarded') {
            $query->has('awards');
        } elseif ($selectedCategory == 'comments') {
            $query->orderByDesc('comment_count');
        }

        //this doesn't work for some reason
        //        if (Auth::user()->creator->id) {
        //            $channelDisinterestIDs = ChannelDisinterest::where('creator_id', Auth::user()->creator->id)
        //                ->pluck('channel_id')
        //                ->toArray();
        //            $query->whereNotIn('creator_id', $channelDisinterestIDs);
        //            $videoDisinterestIDs = videoDisinterest::where('creator_id', Auth::user()->creator->id)
        //                ->pluck('video_id')
        //                ->toArray();
        //            $query->whereNotIn('id', $videoDisinterestIDs);
        //        }


        // Filter by video platform
        if (!empty($selectedVideoPlatforms)) {
            $query->whereIn('preferred_source', $selectedVideoPlatforms);
        }

        // Don't retrieve the same videos
        if ( $videoIds != [] ) {
            $query->whereNotIn('id', $videoIds);
        }

        // Only get public videos
        $query->where('visibility', '=','public');

        // Retrieve the videos
        if ($query->exists()) {
            $videos = $query->take($perPage)
                ->with(array('creator' => function ($q)
                {
                    $q->select(array('id', 'name', 'slug', 'avatar_url'));
                }))
                ->get(['id', 'title', 'slug', 'thumbnail_url', 'preferred_source', 'time_published', 'duration', 'creator_id', 'comment_count', 'views', 'like_count', 'dislike_count', 'visibility']);
        }

        if (!isset($videos) || $videos->count() < $perPage / 2) {
            //get more videos randomly after no records available
            $extraVideos = Video::inRandomOrder()->where('visibility', '=','public')
                ->whereIn('preferred_source', $selectedVideoPlatforms)
                ->whereNotIn('id', $videoIds)
                ->with(array('creator' => function ($q)
                {
                    $q->select(array('id', 'name', 'slug', 'avatar_url'));
                }))
                ->take($perPage )
                ->get(['id', 'title', 'slug', 'thumbnail_url', 'preferred_source', 'time_published', 'duration', 'creator_id', 'comment_count', 'views', 'like_count', 'dislike_count', 'visibility']);

            $videos = $extraVideos;
        }
        $data['data'] = $videos->shuffle();
        $data['category'] = $selectedCategory;
        return $data;
    }

}
