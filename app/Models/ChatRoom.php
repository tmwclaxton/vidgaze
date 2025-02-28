<?php

namespace App\Models;

use App\Models\CommentModels\Comment;
use App\Models\VideoModels\VideoComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        // chatroom -> chatRoomComment -> comments
        return $this->hasManyThrough(Comment::class, ChatRoomComment::class, 'chatroom_id', 'id', 'id', 'comment_id');
    }
}
