<?php

namespace App\Models\StreamModels;

use App\Models\Category;
use App\Models\CommentModels\Comment;
use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    //eager load creator
    protected $with = ['creator'];
    protected $attributes = [
        'viewers' => 0
    ];

    //no mass assignment!
    protected $guarded = [];



    //Alphabetical order

    public function creator() {
    return $this->belongsTo(Creator::class, 'creator_id');
    }
    public function sources() {
        return $this->hasMany(StreamSource::class, 'stream_id');
    }

    public function comments() {
        // stream -> stream_comment -> comments
        return $this->hasManyThrough(Comment::class, StreamComment::class, 'stream_id', 'id', 'id', 'comment_id');
    }

    public function getPreferredSourceID() {
        $streamSource = $this->sources->where('source_name', $this->preferred_source)->first();
        if(!$streamSource) return null;
        return $streamSource['external_id'];
    }
    public function awards() {
        return $this->hasMany(StreamAward::class);
    }
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
