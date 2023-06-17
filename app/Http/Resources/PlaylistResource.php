<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;

class PlaylistResource extends ResourceCollection
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
            'name' => $this->name,
            'server_made' => $this->server_made,
            'visibility' => $this->visibility,
            'description' => $this->description,
            'video_count' => $this->video_count ,
            'recent_video_image' => $this->recent_video_image,
        ];
    }
}
