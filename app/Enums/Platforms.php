<?php

namespace App\Enums;

use InvalidArgumentException;

enum Platforms: string
{
    case YouTube = 'youtube';
    case Dailymotion = 'dailymotion';
    case Vimeo = 'vimeo';
    case Twitch = 'twitch';
    case Rumble = 'rumble';
    case Odysee = 'odysee';
    case Podcasts = 'podcasts';
    case SoundCloud = 'soundcloud';

    public static function fromValue(string $value) : Platforms
    {
        switch($value) {
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
            default:
                throw new InvalidArgumentException('Invalid value for Platform');
        }
    }

    function getPrefix(): string
    {
        return match ($this){
            Platforms::YouTube => 'yt',
            Platforms::Dailymotion => 'dm',
            Platforms::Vimeo => 'v',
            Platforms::Twitch => 't',
            Platforms::Rumble => 'r',
            Platforms::Odysee => 'o',
            Platforms::Podcasts => 'p',
            Platforms::SoundCloud => 'sc',
        };
    }

    function getCategoryIdAttribute(): string
    {
        return match ($this){
            Platforms::YouTube => 'youtube_category_id',
            Platforms::Dailymotion => 'dailymotion_category_id',
            Platforms::Vimeo => 'vimeo_category_id',
            Platforms::Twitch => 'twitch_category_id',
            Platforms::Rumble => 'rumble_category_id',
            Platforms::Odysee => 'odysee_category_id',
            Platforms::Podcasts => 'podcast_category_id',
            Platforms::SoundCloud => 'soundcloud_category_id',
        };
    }

    public function jsonSerialize(): string {
        return serialize($this);
    }
    public static function getSupportedPlatforms($asObject = false): array
    {
        if($asObject){
            return [
                Platforms::YouTube,
                Platforms::Dailymotion,
                Platforms::Vimeo,
                Platforms::Twitch,
                Platforms::Podcasts
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
