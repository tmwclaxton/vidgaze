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
    protected $guarded = [];
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
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }
    public function sources(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoSource::class, 'video_id');
    }
    public function getPrimarySourceID() {
        return $this->sources->where('source_name', $this->preferred_source)->first()['external_id'];
    }
    public function dislikes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoDislike::class);
        //->join('video_dislikes', 'video_dislikes.creator_id', '=', 'creators.id');
    }
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoLike::class);
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
