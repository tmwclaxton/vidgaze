<?php

namespace App\Http\Resources;

use AllowDynamicProperties;
use App\Models\VideoModels\VideoAward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[AllowDynamicProperties] class VideoResource extends JsonResource
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
            'thumbnail_url' => $this->thumbnail_url,
            'like_count' => $this->like_count,
            'dislike_count' => $this->dislike_count,
            'creator' => new CreatorResource($this->creator()->first()),
            'visibility' => $this->visibility,
            'object_awards' => null,

            'comment_count' => number_format_short($this->comment_count) . " " . Str::plural('Comment', $this->comment_count),
            // capitalize the first letter of the preference and if youtube capitalize the 'T' in 'YouTube'
            'preferred_source' => capitalisePlatformName($this->preferred_source),
            'external_id' => $this->getPreferredSourceID(),
            'type' => 'video',
            'time_uploaded' => Carbon::parse($this->time_uploaded)->toDateTimeString(),
            'time_published' => Carbon::parse($this->time_published)->diffForHumans(),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => Carbon::parse($this->updated_at)->diffForHumans(),
            'unadulterated' => [
                'id' => $this->id,
                'slug' => $this->slug,
                'title' => $this->title,
                'description' => $this->description,
                'duration' => $this->duration,
                'view_count' => $this->view_count,
                'live_viewer_count' => $this->live_viewer_count,
                'thumbnail_url' => $this->thumbnail_url,
                'like_count' => $this->like_count,
                'dislike_count' => $this->dislike_count,
                'creator' => $this->creator()->first(),
                'visibility' => $this->visibility,
                'comment_count' => $this->comment_count,
                'preferred_source' => $this->preferred_source,
                'external_id' => $this->getPreferredSourceID(),
                'type' => 'video',
                'time_uploaded' => $this->time_uploaded,
                'time_published' => $this->time_published,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }

}
