<?php

namespace App\Models\PodcastEpisodeModels;

use App\Models\Award;
use App\Models\CommentModels\Comment;
use App\Models\PodcastModels\Podcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class PodcastEpisode extends Model
{
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'time_published' => 'datetime',
        ];
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function comments(): HasManyThrough
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

    public function awards(): HasManyThrough
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
