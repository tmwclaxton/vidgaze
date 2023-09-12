<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\VideoCollection;
use App\Http\Resources\VideoDraftCollection;
use App\Http\Resources\VideoResource;
use App\Models\VideoModels\VideoView;
use App\Models\VideoViews;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudioContentApiController extends Controller
{
    public function content(Request $request)
    {
        $request->validate([
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ]);
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 15;

        $creator = auth()->user()->creator()->first();


        $videoDrafts = $creator->video_drafts()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        $videos = $creator->videos()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'videoDrafts' => new VideoDraftCollection($videoDrafts),
            'videos' => new VideoCollection($videos),
            'streams' => null,
            'podcasts_episodes' => null,

        ];
    }

    public function latestVideo(Request $request) {
        $creator = auth()->user()->creator()->first();
        $video = $creator->videos()
            ->orderBy('created_at', 'desc')
            ->first();
        // add video_id to request
        if (!$video) {
            return [
                'video' => null,
                'analytic' => null,
            ];
        }
        $request->merge(['video_id' => $video->id]);
        $analytic = $this->videoAnalyticArray($request);
        return [
            'video' => new VideoResource($video),
            'analytic' => $analytic,
        ];

    }

    public function videoAnalytic(Request $request) {
        $request->validate([
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
            'video_id' => 'required|integer',
        ]);
        $analytic = $this->videoAnalyticArray($request);
        return $analytic;
    }


    public function analytics() {
        // join video_views and videos and get the avg videos_views.duration, video_id, viewer_id, created_at, title, slug
        $joined = VideoView::join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.creator_id', auth()->user()->creator()->first()->id)
            ->get(['video_views.duration', 'video_views.video_id', 'video_views.viewer_id', 'video_views.created_at', 'videos.title', 'videos.slug']);

        // get this channels' last month views
        $views = $joined->where('created_at', '>=', Carbon::now()->subMonth() )->count();
        $views = number_format_short($views)  . " " . Str::plural('Views', $views);
        // get average view duration
        $averageViewDuration = $joined->where('created_at', '>=', Carbon::now()->subMonth() )->avg('duration') ;
        if ($averageViewDuration) {
            $averageViewDuration = convertDuration($averageViewDuration) . ' Avg View Duration';
        } else {
            $averageViewDuration = null;
        }


        return [
            'views' => $views,
            'viewDuration' => $averageViewDuration
        ];

    }

    public function comments() {
        // there are video comments, channel comments, stream comments and podcast episode comments
        // these are connect like video -> video_comments -> comments etc.

        // get all video comments
        $videos = auth()->user()->creator()->first()->videos()->get(['id']);
        // get comments through video_comments order by lowest reply_count and created_at
        $videoComments = $videos->map(fn ($video) => $video->comments()->get())
            ->flatten();

        // get all stream comments
        $streams = auth()->user()->creator()->first()->streams()->get(['id']);
        // get comments through stream_comments order by lowest reply_count and created_at
        $streamComments = $streams->map(fn ($stream) => $stream->comments()->get())
            ->flatten();

        // get all podcast episode comments
        //$podcastEpisodes = auth()->user()->creator()->first()->podcast_episodes()->get(['id']);
        // get comments through podcast_episode_comments order by lowest reply_count and created_at
        //$podcastEpisodeComments = $podcastEpisodes->map(fn ($podcastEpisode) => $podcastEpisode->comments()->get())
        //    ->flatten()
        //    ->sortBy('created_at')
        //    ->sortBy('reply_count');

        // get all channel comments
        //$channelComments = auth()->user()->creator()->first()->comments()->get()
        //    ->sortBy('created_at')
        //    ->sortBy('reply_count');

        // merge all comments and return comments that haven't been replied to by the creator creator_replied = false && its not the creator's own comment
        $comments = $videoComments->merge($streamComments)->where('creator_replied', false)->where('creator_id', '!=', auth()->user()->creator()->first()->id)
            ->sortBy('created_at')
            ->sortBy('reply_count');


        return [
            'comments' => new CommentCollection($comments),
        ];
    }

    private function videoAnalyticArray($request) {
        $creator = auth()->user()->creator()->first();
        $video = $creator->videos()->where('id', $request->video_id)->first();
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }
        // check if video belongs to this creator
        if ($video->creator_id != $creator->id) {
            return response()->json(['message' => 'Not authorized'], 401);
        }
        // return this month's views avg and total watch time
        $joined = VideoView::join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.creator_id', auth()->user()->creator()->first()->id)
            ->where('video_views.video_id', $request->video_id)
            ->get(['video_views.duration','video_views.end_point', 'video_views.video_id', 'video_views.created_at', 'videos.title', 'videos.slug', 'videos.impressions_count']);

        // get this channels' last month views
        $views = $joined->where('created_at', '>=', Carbon::now()->subMonth() )->count();
        $views = number_format_short($views)  . " " . Str::plural('Views', $views);

        // get average view duration
        $averageViewDuration = $joined->where('created_at', '>=', Carbon::now()->subMonth() )->avg('duration') ;
        if ($averageViewDuration) {
            // change average view duration to a numeric value
            $averageViewDuration = convertDuration(intval($averageViewDuration));
            // get average percentage watched
            $averagePercentageWatched = round($joined->where('created_at', '>=', Carbon::now()->subMonth() )->avg('end_point') / $video->duration * 100, 2) . '%';
        } else {
            $averageViewDuration = null;
            $averagePercentageWatched = null;
        }
        // get total watch time
        $totalWatchTime = $joined->sum('duration');

        // get ctr it's views / impressions
        $ctr = round(intval($views) / intval($video->impressions_count) * 100, 2) . '%';

        return [
            'views' => $views,
            'avg_view_duration' => 'Avg. view duration: ' . $averageViewDuration,
            'avg_percentage_watched' => 'On avg. people watched ' . $averagePercentageWatched . ' of this video',
            'total_watch_time' => 'Total Watch Time: ' . convertDuration($totalWatchTime),
            'end_points' => $joined->pluck('end_point'),
            'ctr' => 'CTR: '.$ctr
        ];
    }



}
