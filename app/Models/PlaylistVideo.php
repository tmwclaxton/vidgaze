<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistVideo extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];
    protected $table = 'playlist_video';

    //Alphabetical order

    public function playlist() {
        return $this->belongsTo(Playlist::class);
    }

    public function video() {
        return $this->belongsTo(Video::class);
    }
}
