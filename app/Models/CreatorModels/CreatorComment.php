<?php

namespace App\Models\CreatorModels;

use App\Models\CommentModels\Comment;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorComment extends Model
{
    public function comment() {
        return $this->hasOne(Comment::class, 'id','comment_id');
    }
    public function creator() {
        return $this->belongsTo(Creator::class);
    }
}
