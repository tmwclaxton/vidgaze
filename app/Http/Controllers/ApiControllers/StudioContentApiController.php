<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

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
}
