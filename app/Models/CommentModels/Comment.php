<?php

namespace App\Models\CommentModels;

use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    //eager load creator
    protected $with = ['owner','interactions'];


    //Alphabetical order

    public function awards() {
        return $this->hasMany(CommentAward::class);
    }
    public function owner() {
        return $this->belongsTo(Creator::class, 'creator_id');
    }
    public function interactions() {
        return $this->hasMany(Creator::class, 'id')
        ->join('comment_interactions', 'comment_interactions.creator_id', '=', 'creators.id');
    }
//    public function likes() {
//        return $this->hasMany(Creator::class, 'id')
//        ->join('comment_likes', 'comment_likes.creator_id', '=', 'creators.id');
//    }
    public function video() {
        return $this->belongsTo(Video::class);
    }
    public function replies() {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }
    public function parent() {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

}
