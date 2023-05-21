<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\PodcastModels\Podcast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisode extends Model
{
    use HasFactory;

    protected $guarded = [];

    // TODO morphto awards, comments, likes, dislikes, playlists

    public function podcast() {
        return $this->belongsTo(Podcast::class);
    }

}
