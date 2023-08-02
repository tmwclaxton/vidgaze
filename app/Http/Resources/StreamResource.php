<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StreamResource extends JsonResource
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
            'language' => $this->language,
            'is_live' => $this->is_live ? true : false,
            'tags' => $this->tags,
            'category' => new CategoryResource($this->category),
            'preferred_source' => capitalisePlatformName($this->preferred_source),
            'viewers' =>  number_format_short($this->viewers) . " " . Str::plural('Viewer', $this->viewers) ,
            'live_viewer_count' => number_format_short($this->live_viewer_count),
            'thumbnail_url' => $this->thumbnail_url,
            'creator' => new CreatorResource( $this->creator()->first() ),
            'external_id' => $this->getPreferredSourceID(),
            'type' => 'stream',
        ];
    }
}
