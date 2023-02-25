<?php

namespace App\Models;

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
        'views' => 0
    ];

    protected function views() : Attribute
    {
        return Attribute::make(get: fn($value)=>$value ?? 0);
    }

//    public static function findOrCreate(string $externalVideoID, Platforms $source): Video
//    {
//        $video_source = VideoSource::where('external_id', '=', $externalVideoID)
//            ->where('source_name', '=', $source->name)->first();
//        if(!$video_source)
//        {
//            return match($source){
//                Platforms::YouTube => YouTube::makeVideoModel($externalVideoID),
//                Platforms::Dailymotion => Dailymotion::makeVideoModel($externalVideoID),
//                Platforms::Vimeo => Vimeo::makeVideoModel($externalVideoID),
//            };
//        }
//        else{
//            return $video_source->video()->first();
//        }
//    }

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
