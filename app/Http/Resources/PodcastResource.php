<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'rss_url' => $this->rss_url,
            'apple_podcast_id' => $this->apple_podcast_id,
            'like_count' => (int) $this->like_count,
            'view_count' => (int) $this->view_count,
            'live_viewer_count' => (int) $this->live_viewer_count,
            'creator' => $this->creator ? CreatorResource::make($this->creator) : null,
        ];
    }
}
