<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // TODO: no mass assignment!
    protected $guarded = [''];


    public function frontEndDetails(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'tags' => json_decode($this->tags_json),
            'thumbnail_url' => $this->thumbnail_url,
        ];
    }


    public function creators() {
        return $this->hasMany(Creator::class);
    }
    public function streams() {
        return $this->hasMany(Stream::class);
    }
    public function videos() {
        return $this->hasMany(Video::class);
    }
}
