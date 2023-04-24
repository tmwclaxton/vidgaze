<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    //Alphabetical order

    public function owner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Creator::class, 'id', 'creator_id');
    }

    public function videos(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Video::class, PlaylistVideo::class, 'playlist_id', 'id', 'id', 'video_id');
    }




}
