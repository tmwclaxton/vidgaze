<?php

namespace App\Http\Resources;

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
            'email_verified_at' => $this->email_verified_at,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => new CreatorResource( $this->creator ),

        ];
    }
}
