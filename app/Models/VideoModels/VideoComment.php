<?php

namespace App\Models\VideoModels;

use App\Models\CommentModels\Comment;
use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    public function comment() {
        return $this->hasOne(Comment::class, 'id','comment_id');
    }
    public function video() {
        return $this->belongsTo(Video::class);
    }
}
