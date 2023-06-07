<?php

namespace App\Enums;

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
    public static function getSupportedPlatform($asObject = false): array
    {
        if($asObject){
            return [
                Platform::YouTube,
                Platform::Dailymotion,
                Platform::Vimeo,
                Platform::Twitch,
                Platform::Podcasts
            ];
        }
        return [
            "youtube",
            "dailymotion",
            "vimeo",
            "twitch",
            "podcasts"
        ];
    }
}
