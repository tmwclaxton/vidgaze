<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PodcastEpisodeResource extends JsonResource
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
            'audio_url' => $this->audio_url,
            'duration' => $this->duration,
            'thumbnail_url' => $this->thumbnail_url,
            'time_published' => $this->time_published?->toIso8601String(),
            'podcast_slug' => $this->whenLoaded('podcast', fn () => $this->podcast->slug),
            'podcast_title' => $this->whenLoaded('podcast', fn () => $this->podcast->title),
        ];
    }
}
