<?php

namespace App\Models\CommentModels;

use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorComment;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastEpisodeModels\PodcastEpisodeComment;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamComment;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoComment;
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

    // a comment has a stream / video / podcast episode / channel that it belongs to through a video comment / podcast comment / channel comment / stream comment
    public function hasOneThroughObject(String $objectType) {
        switch($objectType) {
            case 'video':
                return $this->hasOneThrough(Video::class, VideoComment::class, 'comment_id', 'id', 'id', 'video_id')->first();
            case 'podcast':
                return $this->hasOneThrough(PodcastEpisode::class, PodcastEpisodeComment::class, 'comment_id', 'id', 'id', 'podcast_episode_id')->first();
            case 'stream':
                return $this->hasOneThrough(Stream::class, StreamComment::class, 'comment_id', 'id', 'id', 'stream_id')->first();
            case 'channel':
                return $this->hasOneThrough(Creator::class, CreatorComment::class, 'comment_id', 'id', 'id', 'creator_id')->first();
            default:
                return null;
        }

    }


    public function replies() {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }
    public function parent() {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

}
