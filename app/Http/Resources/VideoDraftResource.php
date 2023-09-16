<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VideoDraftResource extends JsonResource
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
            'thumbnail_path' => $this->thumbnail_path,
            'video_path' => $this->video_path,
            'creator' => new CreatorResource($this->creator()->first()),
            'preferred_source' => $this->preferred_source,
            'type' => 'video_draft',
            'tags' => json_decode($this->tags) ?? [],
            'language' => $this->language,
            'region' => $this->region,
            'audience' => $this->audience,
            'category' => new CategoryResource($this->category()->first()),
            'platforms' => json_decode($this->platforms) ?? [],
            'visibility' => $this->visibility,
            'publish_time' => $this->publish_time ? $this->publish_time : Carbon::now()->toDateTimeString(),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => Carbon::parse($this->updated_at)->diffForHumans(),
        ];
    }
}
