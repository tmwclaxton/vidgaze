<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => convertDuration($this->duration),
            'view_count' =>  number_format_short($this->view_count) . " " . Str::plural('View', $this->view_count) ,
            'live_viewer_count' => number_format_short($this->live_viewer_count),
            'time_uploaded' => Carbon::parse($this->time_uploaded)->toDateTimeString(),
            'time_published' => Carbon::parse($this->time_published)->diffForHumans(),
            'thumbnail_url' => $this->thumbnail_url,
            'likes' => $this->like_count,
            'dislikes' => $this->dislike_count,
            'creator' => new CreatorResource($this->creator()->first()),
            'comment_count' => number_format_short($this->comment_count),
            // capitalize the first letter of the preference and if youtube capitalize the 'T' in 'YouTube'
            'preferred_source' => capitalisePlatformName($this->preferred_source),
            'external_id' => $this->getPreferredSourceID(),
            'type' => 'video',
        ];
    }
}
