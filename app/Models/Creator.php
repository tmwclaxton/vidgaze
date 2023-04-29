<?php

namespace App\Models;

use App\Enums\Platforms;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Creator extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = ['id'];

//    protected $with = ['sources']; //eager load creator sources

    public function frontEndDetails(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'banner_url' => $this->banner_url,
            'karma' => $this->karma,
            'subscriber_count' => $this->subscriber_count,
            'is_live' => $this->is_live,
            'contact_email' => $this->contact_email,
            ];
    }


    public function updateAllContentByApi() : void
    {
        foreach ($this->sources()->get() as $source){
            match($source->source_name){
                Platforms::YouTube->name => YouTube::updateAllChannelContent($source->external_channel_id),
                Platforms::Dailymotion->name => Dailymotion::updateAllChannelContent($source->external_channel_id),
                Platforms::Vimeo->name => Vimeo::updateAllChannelContent($source->external_channel_id),
                default => dd('not in match statement')
            };
        }
    }

    public function updateContentByApiBeforeDate(\Carbon $date)
    {
        foreach ($this->sources()->get() as $source){
            match($source->source_name){
                Platforms::YouTube->name => YouTube::updateAllChannelContent($source->external_channel_id),
                Platforms::Dailymotion->name => Dailymotion::updateAllChannelContent($source->external_channel_id),
                default => dd('not in match statement')
            };
        }
    }


    public static function findOrCreate(string $externalChannelID, Platforms $source) : Creator
    {
        $creator_source = CreatorSource::where('external_channel_id', '=', $externalChannelID)
            ->where('source_name', '=', $source->name)->first();
        if(!$creator_source)
        {
            return match($source){
                Platforms::YouTube => YouTube::makeCreatorModel($externalChannelID),
                Platforms::Dailymotion => Dailymotion::makeCreatorModel($externalChannelID),
                Platforms::Vimeo => Vimeo::makeCreatorModel($externalChannelID),
                Platforms::Twitch => Twitch::makeCreatorModel($externalChannelID),
            };
        }
        return $creator_source->creator()->first();
    }

    //Alphabetical order

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
    public function channelDisinterests(): HasMany
    {
        return $this->hasMany(channelDisinterest::class, 'creator_id');
    }
    public function videoDisinterests(): HasMany
    {
        return $this->hasMany(videoDisinterest::class, 'creator_id');
    }
    public function comment_interactions(): HasMany
    {//might be broken //I think this is more broken now soz
        return $this->hasMany(CommentInteraction::class)
        ->join('comment_interactions', 'comment_interactions.creator_id', '=', 'creators.id');
    }
    public function sources(): HasMany
    {
        return $this->hasMany(CreatorSource::class, 'creator_id');
    }
    public function comment_awards(): HasMany
    {
        return $this->hasMany(CommentAward::class, 'giver_id');
    }
    public function creator_category(): HasOne
    {
        return $this->hasOne(Category::class);
    }
    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }
    public function getServerMadePlaylist($name)
    {   //for getting watch later / disliked / liked playlists
        return $this->playlists()->whereName($name)->whereServerMade(true)->get()->first();
    }
    public function podcasts(): HasMany {
        return $this->hasMany(Podcast::class);
    }
    public function streams(): HasMany {
        return $this->hasMany(Stream::class);
    }
    public function stream_awards(): HasMany
    {
        return $this->hasMany(StreamAward::class, 'giver_id');
    }
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Creator::class, 'subscriptions', 'subscriber_id');
    }
    public function subscribers(): HasManyThrough
    {
        return $this->hasManyThrough(Creator::class, Subscription::class, 'creator_id', 'id', 'id', 'subscriber_id');
    }
    public function unions(): HasMany
    {
        return $this->hasMany(Union::class, 'owner_id');
    }
    public function union_memberships(): BelongsToMany
    {
        return $this->belongsToMany(Union::class, 'union_memberships', 'member_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
    public function video_upload(): HasOne
    {
        return $this->hasOne(VideoUpload::class);
    }
    public function video_awards(): HasMany
    {
        return $this->hasMany(VideoAward::class, 'giver_id');
    }
    public function video_views(): HasMany
    {
        return $this->hasMany(VideoViews::class, 'viewer_id');
    }
    public function video_view_info(): HasMany
    {
        return $this->hasMany(VideoViewInfos::class, 'viewer_id');
    }






}
