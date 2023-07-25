<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class PlaylistResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'creator' => new CreatorResource($this->owner),
            'name' => $this->name,
            'server_made' => $this->server_made ? true : false,
            'visibility' => $this->visibility,
            'description' => $this->description,
            'video_count' => $this->video_count ,
            'recent_video_image' => $this->recent_video_image,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => Carbon::parse($this->updated_at)->diffForHumans(),
            'videos_present_in_playlist' => $this->videos_present_in_playlist ?? false,
        ];
    }


}
