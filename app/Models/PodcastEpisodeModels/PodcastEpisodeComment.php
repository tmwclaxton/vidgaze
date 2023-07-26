<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\CommentModels\Comment;
use App\Models\VideoModels\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisodeComment extends Model
{
    public function comment() {
        return $this->hasOne(Comment::class, 'id','comment_id');
    }
    public function podcastEpisode() {
        return $this->belongsTo(PodcastEpisode::class);
    }
}
