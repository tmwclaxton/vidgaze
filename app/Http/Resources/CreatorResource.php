<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorResource extends JsonResource
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
            'name' => $this->name,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'banner_url' => $this->banner_url,
            'karma' => $this->karma,
            'subscriber_count' => $this->subscriber_count,
            'is_live' => $this->is_live,
            'contact_email' => $this->contact_email,
        ];
    }
}
