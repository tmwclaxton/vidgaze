<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

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
        'views' => 0
    ];

    //this is being used in the frontend and because I can't use functions in the frontend I have to use this
    public function frontEndDetails(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'duration' => convertDuration($this->duration),
            'views' =>  number_format_short($this->views) . " " . Str::plural('View', $this->views) ,
            'live_viewer_count' => number_format_short($this->live_viewer_count),
            'time_uploaded' => Carbon::parse($this->time_uploaded)->toDateTimeString(),
            'time_published' => Carbon::parse($this->time_published)->diffForHumans(),
            'thumbnail_url' => $this->thumbnail_url,
            'likes' => $this->like_count,
            'dislikes' => $this->dislike_count,
            'creator' => $this->creator()->first()->frontEndDetails(),
        ];
    }

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
        return $this->hasMany(VideoViews::class);
    }
    public function view_info(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VideoViewInfos::class);
    }

}
