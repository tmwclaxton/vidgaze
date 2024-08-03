<?php

namespace App\Http\Resources;

use App\Models\StreamModels\StreamAward;
use App\Models\VideoModels\VideoAward;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'object_awards' => StreamAward::where('stream_id', '=', $this->id)
                ->groupBy('award_id')
                ->select('award_id', DB::raw('count(*) as total'))
                ->get()->sortByDesc('award.coin_price'),
            'tags' => $this->tags,
            'category' => new CategoryResource($this->category),
            'preferred_source' => capitalisePlatformName($this->preferred_source),
            'viewers' =>  number_format_short($this->viewers) . " " . Str::plural('Viewer', $this->viewers) ,
            'live_viewer_count' => number_format_short($this->live_viewer_count),
            'thumbnail_url' => $this->thumbnail_url,
            'creator' => new CreatorResource( $this->creator()->first() ),
            'external_id' => capitalisePlatformName($this->preferred_source) === "Twitch" ? $this->creator()->first()->name : $this->getPreferredSourceID(),
            'type' => 'stream',
        ];
    }
}
