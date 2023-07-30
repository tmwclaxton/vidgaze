<?php

namespace App\Models\CreatorModels;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Models\Category;
use App\Models\CommentModels\Comment;
use App\Models\CommentModels\CommentAward;
use App\Models\CommentModels\CommentInteraction;
use App\Models\PlaylistModels\Playlist;
use App\Models\PodcastModels\Podcast;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamAward;
use App\Models\Union;
use App\Models\User;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoAward;
use App\Models\VideoModels\VideoDisinterest;
use App\Models\VideoModels\VideoDraft;
use App\Models\VideoModels\VideoUpload;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
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

    public function updateAllContentByApi() : void
    {
        foreach ($this->sources()->get() as $source){
            match($source->source_name){
                Platform::YouTube->name => YouTube::updateAllChannelContent($source->external_channel_id),
                Platform::Dailymotion->name => Dailymotion::updateAllChannelContent($source->external_channel_id),
                Platform::Vimeo->name => Vimeo::updateAllChannelContent($source->external_channel_id),
                default => dd('not in match statement')
            };
        }
    }

    public function updateContentByApiBeforeDate(\Carbon $date)
    {
        foreach ($this->sources()->get() as $source){
            match($source->source_name){
                Platform::YouTube->name => YouTube::updateAllChannelContent($source->external_channel_id),
                Platform::Dailymotion->name => Dailymotion::updateAllChannelContent($source->external_channel_id),
                default => dd('not in match statement')
            };
        }
    }


    public static function findOrCreate(string $externalChannelID, Platform $source) : Creator
    {
        $creator_source = CreatorSource::where('external_channel_id', '=', $externalChannelID)
            ->where('source_name', '=', $source->name)->first();
        if(!$creator_source)
        {
            return match($source){
                Platform::YouTube => YouTube::makeCreatorModel($externalChannelID),
                Platform::Dailymotion => Dailymotion::makeCreatorModel($externalChannelID),
                Platform::Vimeo => Vimeo::makeCreatorModel($externalChannelID),
                Platform::Twitch => Twitch::makeCreatorModel($externalChannelID),
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
    public function creator_interactions(): HasMany
    {
        return $this->hasMany(CreatorInteraction::class, 'viewer_id');
    }

    public function video_interactions(): HasMany
    {
        return $this->hasMany(VideoInteraction::class, 'viewer_id');
    }
    public function comment_interactions(): HasMany
    {//might be broken //I think this is more broken now soz
        return $this->hasMany(CommentInteraction::class, 'creator_id');
        //->join('comment_interactions', 'comment_interactions.creator_id', '=', 'creators.id');
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
        return $this->belongsToMany(Creator::class, 'creator_interactions', 'viewer_id')->where('subscribed', '=', 1);
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
        return $this->hasMany(VideoView::class, 'viewer_id');
    }

    public function video_drafts(): HasMany
    {
        return $this->hasMany(VideoDraft::class);
    }

}
