<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\VideoModels\VideoView;
use App\Models\VideoViews;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StudioContentApiController extends Controller
{
    public function index()
    {
        $creator = auth()->user()->creator()->first();
        $videoDrafts = $creator->video_drafts()->get(['id', 'title', 'description', 'slug', 'thumbnail_path', 'visibility', 'created_at']);
        $videos = $creator->videos()->get(['id', 'title', 'description', 'slug', 'thumbnail_url', 'visibility','duration', 'time_uploaded', 'time_published', 'view_count', 'comment_count', 'like_count', 'dislike_count'])
            ->map(fn ($video) => [
                'id' => $video->id,
                'title' => $video->title,
                'description' => $video->description,
                'slug' => $video->slug,
                'thumbnail_url' => $video->thumbnail_url,
                'visibility' => $video->visibility,
                'duration' => $video->duration,
                'time_uploaded' => Carbon::create($video->time_uploaded)->format('j M Y'),
                'time_published' => $video->time_published ? Carbon::create($video->time_published)->format('j M Y') : null,
                'view_count' => $video->view_count,
                'comment_count' => $video->comment_count,
                'like_count' => $video->like_count,
                'dislike_count' => $video->dislike_count,
            ]);

        return ['videoDrafts' => $videoDrafts, 'videos' => $videos];
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
}
