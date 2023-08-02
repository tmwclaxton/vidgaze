<?php

namespace App\Models\VideoModels;

use App\Models\Category;
use App\Models\CommentModels\Comment;
use App\Models\CreatorModels\Creator;
use App\Models\PlaylistModels\Playlist;
use App\Models\VideoDislike;
use App\Models\VideoLike;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $views
 */
class Video extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = ['id'];
    protected $with = ['creator'];
    protected $dates = [
        'time_uploaded',
        'time_published',
    ];
    protected $attributes = [
        'view_count' => 0
    ];



    protected function views() : Attribute
    {
        return Attribute::make(get: fn($value)=>$value ?? 0);
    }
    //Alphabetical order

    public function awards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoAward::class);
    }
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        // video -> video_comment -> comments
        return $this->hasManyThrough(Comment::class, VideoComment::class, 'video_id', 'id', 'id', 'comment_id');
    }
    public function sources(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoSource::class, 'video_id');
    }
    public function getPreferredSourceID() {
        $videoSource = $this->sources->where('source_name', $this->preferred_source)->first();
        if(!$videoSource) return null;
        return $videoSource['external_id'];
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
    public function playlists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_video');
    }
    public function viewed(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoView::class);
    }
    public function interactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoInteraction::class);
    }

}
