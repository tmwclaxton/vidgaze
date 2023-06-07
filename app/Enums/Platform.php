<?php

namespace App\Enums;

use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\iIsPlatform;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use InvalidArgumentException;

enum Platform: string
{
    case VidGaze = 'vidgaze';
    case YouTube = 'youtube';
    case Dailymotion = 'dailymotion';
    case Vimeo = 'vimeo';
    case Twitch = 'twitch';
    case Rumble = 'rumble';
    case Odysee = 'odysee';
    case Podcasts = 'podcasts';
    case SoundCloud = 'soundcloud';
    case Spotify = 'spotify';
    case Instagram = 'instagram';

    public static function fromValue(string $value) : Platform
    {
        switch($value) {
            case 'vidgaze':
                return self::VidGaze;
            case 'youtube':
                return self::YouTube;
            case 'dailymotion':
                return self::Dailymotion;
            case 'vimeo':
                return self::Vimeo;
            case 'twitch':
                return self::Twitch;
            case 'rumble':
                return self::Rumble;
            case 'odysee':
                return self::Odysee;
            case 'podcasts':
                return self::Podcasts;
            case 'soundcloud':
                return self::SoundCloud;
            case 'spotify':
                return self::Spotify;
            case 'instagram':
                return self::Instagram;
            default:
                throw new InvalidArgumentException('Invalid value for Platform');
        }
    }

    function getPrefix(): string
    {
        return match ($this){
            Platform::VidGaze => 'vg',
            Platform::YouTube => 'yt',
            Platform::Dailymotion => 'dm',
            Platform::Vimeo => 'vm',
            Platform::Twitch => 'tw',
            Platform::Rumble => 'rm',
            Platform::Odysee => 'od',
            Platform::Podcasts => 'pc',
            Platform::SoundCloud => 'sc',
            Platform::Spotify => 'sp',
            Platform::Instagram => 'ig',
        };
    }

    function getCategoryIdAttribute(): string
    {
        return match ($this){
            Platform::VidGaze => 'vidgaze_category_id',
            Platform::YouTube => 'youtube_category_id',
            Platform::Dailymotion => 'dailymotion_category_id',
            Platform::Vimeo => 'vimeo_category_id',
            Platform::Twitch => 'twitch_category_id',
            Platform::Rumble => 'rumble_category_id',
            Platform::Odysee => 'odysee_category_id',
            Platform::Podcasts => 'podcast_category_id',
            Platform::SoundCloud => 'soundcloud_category_id',
            Platform::Spotify => 'spotify_category_id',
            Platform::Instagram => 'instagram_category_id',
        };
    }

    public function jsonSerialize(): string {
        return serialize($this);
    }



    public static function getSupportedPlatforms(bool $asEnum = false, bool $asPrefix = false): \Illuminate\Support\Collection
    {
        $supported = collect([
            Platform::YouTube,
            Platform::Dailymotion,
            Platform::Vimeo,
            Platform::Twitch,
            Platform::Podcasts
        ]);
        if($asEnum){
            return $supported;
        }
        if($asPrefix){
            return $supported->map(fn($platform) => $platform->getPrefix());
        }
        return $supported->map(fn($platform) => $platform->value);
    }

    function getPlatformClass(): iIsPlatform
    {
        return match ($this){
//            Platform::VidGaze => 'vg',
            Platform::YouTube => new YouTube,
            Platform::Dailymotion => new Dailymotion,
            Platform::Vimeo => new Vimeo,
            Platform::Twitch => new Twitch,
//            Platform::Rumble => 'rm',
//            Platform::Odysee => 'od',
//            Platform::Podcasts => 'pc',
//            Platform::SoundCloud => 'sc',
//            Platform::Spotify => 'sp',
//            Platform::Instagram => 'ig',
        };
    }
}
