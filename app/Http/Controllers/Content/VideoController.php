<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoCollection;
use App\Http\Resources\VideoResource;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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

    public function show(Video $video)
    {
        $this->checkVisibilityAndOwnership($video);
        return Inertia::render('Viewer/Watch/Watch', [
            'item' => new VideoResource($video),
            'type' => 'video'
        ]);
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
        $first_video_slug = $request->input('first_video_slug') ?? null;


        if (!is_array($videoIds) ) {
            //explode the ids into an array
            $videoIds = explode(',', $videoIds);
        }

        // if first_video_id is set add it to videoIds to be ignored
        if ($first_video_slug) {
            $videoIds[] = $first_video_slug;
        }

        $query = Video::query();

        $selectedVideoPlatforms = ['YouTube', 'Dailymotion', 'Vimeo'];

        if ($selectedCategory == 'popular') {

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

        } elseif ($selectedCategory == 'trending') {
            if (!isset($mostTrendingVideoIds)) {
                $videoViewInfos = DB::table('video_interactions')
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
        if ( $videoIds != [] ) {
            $query->whereNotIn('id', $videoIds);
            //return ($videoIds);
        }


        $videos = $query->take($perPage)->get();


        // If there are not enough videos, get random public videos
        if (!isset($videos) || $videos->count() < $perPage) {
            // get random public videos that are not in the videoIds array and get the amt to make up the difference if there are some videos already
            if (isset($videos)) {
                $amt = $perPage - $videos->count();
            } else {
                $amt = $perPage;
            }
            $randomVideos = new VideoCollection(Video::where('visibility', 'public')->whereNotIn('id', $videoIds)->inRandomOrder()->take($amt)->get());
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



        $data['videos'] = $videos;
        $data['category'] = $selectedCategory;
        $data['perPage'] = $perPage;
        $data['ids'] = $videoIds;
        return $data;
    }

    private function checkVisibilityAndOwnership($item) {
        //forbidden if visibility is set to private and you don't own it
        if ($item->visibility == 'private' && $item->creator_id != Auth::user()->creator->id) {
            abort(401);
        }
    }
}
