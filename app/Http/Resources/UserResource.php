<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'DOB' => $this->DOB,
            'email' => $this->email,
            // check if not null
            'email_verified_at' => $this->email_verified_at ? Carbon::parse($this->email_verified_at)->diffForHumans() : null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
            'updated_at' => Carbon::parse($this->updated_at)->diffForHumans(),
            'creator' => new CreatorResource( $this->creator ),

        ];
    }
}
