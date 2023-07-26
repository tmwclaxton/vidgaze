<?php

namespace App\Models\StreamModels;

use App\Models\CommentModels\Comment;
use Illuminate\Database\Eloquent\Model;

class StreamComment extends Model
{
    public function comment() {
        return $this->hasOne(Comment::class, 'id','comment_id');
    }
    public function stream() {
        return $this->belongsTo(Stream::class);
    }
}
