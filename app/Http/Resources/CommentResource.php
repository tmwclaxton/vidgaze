<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CommentResource extends JsonResource
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
            'body' => $this->body,
            'owner' => new CreatorResource($this->owner()->first()),
            'like_count' => $this->like_count,
            'dislike_count' => $this->dislike_count,
            'reply_count' => $this->reply_count,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            // if created_at != updated_at then it has been edited
            'edited' =>  $this->created_at != $this->updated_at ? true : false,
            'pinned' => $this->pinned,
            'parent_comment_id' => $this->parent_comment_id,

        ];
    }
}
