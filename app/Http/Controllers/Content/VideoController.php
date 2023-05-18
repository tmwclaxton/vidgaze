<?php

namespace App\Http\Controllers\Content;

use App\Helpers\Tokens\TokenHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoCollection;
use App\Models\channelDisinterest;
use App\Models\Playlist;
use App\Models\PlaylistVideo;
use App\Models\Video;
use App\Models\videoDisinterest;
use App\Models\videoReport;
use App\Models\VideoViewInfos;
use App\Models\VideoViews;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use function Deployer\Support\array_merge_alternate;

class VideoController extends Controller
{
    //the 7 restful routes
    // index - show all
    // show - show one
    // create - show a page to create one of those item
    // store - when form submited persist the item
    // edit - show page to edit the item
    // update - when form submitted save the edits
    // destroy - delete one item

    public function index()
    {
        return Inertia::render('Viewer/Videos/VideosIndex');
    }
    public function shorts()
    {
        return Inertia::render('Viewer/Shorts/ShortsIndex');
    }
    public function infinite(Request $request)
    {

        $perPage = $request->perPage ?? 20;
        //get ids from params
        $videoIds = $request->videoIds ?? [];
        // Get the selected category
        $selectedCategory = $request->input('category') ?? 'popular';
        $shorts = $request->input('shorts') ?? false;

        if (!is_array($videoIds) ) {
            //explode the ids into an array
            $videoIds = explode(',', $videoIds);
        }

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
                //$query->whereIn('id', $mostPopularVideoIds)->orderByRaw(DB::raw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")"));
                $query->whereIn('id', $mostPopularVideoIds)->orderByRaw("FIELD(id, ".implode(',', $mostPopularVideoIds->toArray()).")");

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
                //$query->whereIn('id', $mostTrendingVideoIds)->orderByRaw(DB::raw("FIELD(id, " . implode(',', $mostTrendingVideoIds->toArray()) . ")"));
                $query->whereIn('id', $mostTrendingVideoIds)->orderByRaw("FIELD(id, " . implode(',', $mostTrendingVideoIds->toArray()) . ")");

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

        // Only get public videos
        $query->where('visibility', '=','public');

        if ($shorts) {
            $query->where('duration', '<=', 60);
        }

        // Filter by video platform
        if (!empty($selectedVideoPlatforms)) {
            $query->whereIn('preferred_source', $selectedVideoPlatforms);
        }

        //this doesn't work for some reason
        if (Auth::user()) {
            $channelDisinterestIDs = ChannelDisinterest::where('creator_id', Auth::user()->creator->id)
                ->pluck('channel_id')
                ->toArray();
            $query->whereNotIn('creator_id', $channelDisinterestIDs);
            $videoDisinterestIDs = videoDisinterest::where('creator_id', Auth::user()->creator->id)
                ->pluck('video_id')
                ->toArray();
            $query->whereNotIn('id', $videoDisinterestIDs);
        }

        // Don't retrieve the same videos
        if ( $videoIds != [] ) {
            $query->whereNotIn('id', $videoIds);
            //return ($videoIds);
        }


        // Retrieve the videos
        if ($query->exists()) {
            $videos = new VideoCollection($query->take($perPage)->get());
        }

        // error handling
        if (!isset($videos)) {
            return response()->json([
                'error' => 'No videos found'
            ], 404);
        }


        $data['data'] = $videos->shuffle();
        $data['category'] = $selectedCategory;
        $data['perPage'] = $perPage;
        $data['ids'] = $videoIds;
        return $data;
    }


    // this is for the video modal
    public function details(Request $request, $videoId)
    {
        $video = Video::findOrFail($videoId);

        // check if video exists
        if (!$video) {
            return response()->json([
                'error' => 'Video not found'
            ], 404);
        }

        // check if user is authenticated
        if (!Auth::user()) {
            return response()->json([
                'error' => 'You are not authenticated'
            ], 401);
        }

        $creatorId = Auth::user()->creator->id;

        // check if video is in watch later playlist
        $inWatchLater = false;
        if ($watchLaterPlaylist = Playlist::where('name', 'Watch Later')->where('creator_id', $creatorId)->first()) {
            if (PlaylistVideo::where('playlist_id', $watchLaterPlaylist->id)->where('video_id', $videoId)->first()) {
                $inWatchLater = true;
            }
        }

        // check if user has disinterested video
        $videoDisinterest = VideoDisinterest::where('creator_id', $creatorId)->where('video_id', $videoId)->exists();

        // check if user has disinterested channel
        $channelDisinterest = ChannelDisinterest::where('creator_id', $creatorId)->where('channel_id', $video->creator->id)->exists();

        // check if user has liked video
        $videoLike = VideoViewInfos::where('viewer_id', $creatorId)->where('video_id', $videoId)->pluck('liked')->first() ?? null;


        return response()->json([
            'inWatchLater' => $inWatchLater,
            'videoDisinterest' => $videoDisinterest,
            'channelDisinterest' => $channelDisinterest,
            'videoLike' => $videoLike,
        ], 200);
    }

    public function report(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $video->increment('report_count');
        return response()->json(['message' => 'Report added successfully.'],200);
    }


    public function like($videoId)
    {
        //return $videoId;
        $video = Video::findOrFail($videoId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }

        [$VideoViewInfos, $likedPlaylist] = $this->fetchVideoViewInfosAndLikedPlaylist($videoId);

        //if they change their rating from dislike to like
        $likedPlaylist->addVideo($video->id);
        $message = 'Liked successfully';

        if ($VideoViewInfos->liked == 'dislike') {
            $video->dislike_count--;
        }

        if ($VideoViewInfos->liked != 'like') {
            $VideoViewInfos->liked = 'like';
            $video->like_count++;
        } else {
            // Have they already liked it
            $VideoViewInfos->liked = null;
            $video->like_count--;
            $likedPlaylist->removeVideo($video->id);
            $message = 'Like removed successfully';
        }

        $VideoViewInfos->save();
        $video->save();
        return response()->json([
            'message' => $message,
            'result' => $VideoViewInfos->liked,
        ], 200);
    }

    public function dislike($videoId)
    {
        $video = Video::findOrFail($videoId);

        if (!isset(Auth::user()->creator)) {
            return response()->json(['message' => 'You are not authenticated.'], 401);
        }
        $message = 'Disliked successfully';

        [$VideoViewInfos, $likedPlaylist] = $this->fetchVideoViewInfosAndLikedPlaylist($videoId);

        //if they change their rating from like to dislike
        if ($VideoViewInfos->liked == 'like') {
            $likedPlaylist->removeVideo($video->id);
            $video->like_count--;
        }

        if ($VideoViewInfos->liked != 'dislike') {
            $VideoViewInfos->liked = 'dislike';
            $video->dislike_count++;
            $liked = 'dislike';
        } else {
            // Have they already disliked it
            $VideoViewInfos->liked = null;
            $video->dislike_count--;
            $liked = null;
            $message = 'Dislike removed successfully';
        }

        $VideoViewInfos->save();
        $video->save();
        return response()->json([
            'message' => $message,
            'result' => $VideoViewInfos->liked,
        ], 200);
    }

    private function fetchVideoViewInfosAndLikedPlaylist($id)
    {
        $VideoViewInfos = VideoViewInfos::where('video_id', $id)
            ->where('viewer_id', Auth::user()->creator->id)
            ->first();

        if (!$VideoViewInfos) {
            $VideoViewInfos = new VideoViewInfos();
            $VideoViewInfos->video_id = $id;
            $VideoViewInfos->viewer_id = Auth::user()->creator->id;
            $VideoViewInfos->save();
        }

        $likedPlaylist = Auth::user()->creator->getServerMadePlaylist('Liked Videos');


        return [$VideoViewInfos, $likedPlaylist];
    }

}
