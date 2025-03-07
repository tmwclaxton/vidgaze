<?php

namespace App\Models;

use App\Models\CommentModels\Comment;
use App\Models\VideoModels\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoomComment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['comment'];

    public function comment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Comment::class, 'id','comment_id');
    }
    public function chatroom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }
}
