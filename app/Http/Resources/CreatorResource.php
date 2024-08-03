<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'bio' => json_decode($this->bio),
            'avatar_url' => $this->avatar_url,
            'banner_url' => $this->banner_url,
            'karma' => number_format_short($this->karma),
            'vidcoins' => number_format_short($this->coins) . " " . Str::plural('Vidcoin', $this->coins),
            'vidcoins_int' => $this->coins,
            'subscriber_count' => number_format_short($this->subscriber_count)  . " " . Str::plural('Subscriber', $this->subscriber_count) ,
            'is_live' => $this->is_live ? true : false,
            'contact_email' => $this->contact_email,
            // get name of each source by plucking source_name and use capitalisePlatformName for each one to format it
            'sources' => $this->sources->pluck('source_name')->map(function ($source) {
                return capitalisePlatformName($source);
            }),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => Carbon::parse($this->updated_at)->diffForHumans(),
        ];
    }
}
