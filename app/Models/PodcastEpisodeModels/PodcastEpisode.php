<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\Award;
use App\Models\CommentModels\Comment;
use App\Models\PodcastModels\Podcast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PodcastEpisode extends Model
{

    protected $guarded = [];


    public function podcast(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        // podcast episode -> podcast episode comments -> comments
        return $this->hasManyThrough(
            Comment::class,
            PodcastEpisodeComment::class,
            'podcast_episode_id',
            'id',
            'id',
            'comment_id'
        );

    }

    public function awards(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        // podcast episode -> podcast episode awards -> awards
        return $this->hasManyThrough(
            Award::class,
            PodcastEpisodeAward::class,
            'podcast_episode_id',
            'id',
            'id',
            'award_id'
        );
    }

}
