<?php

namespace App\Enums;

use App\Helpers\PlatformAPIs\AuthDailymotion;
use App\Helpers\PlatformAPIs\AuthTwitch;
use App\Helpers\PlatformAPIs\AuthVimeo;
use App\Helpers\PlatformAPIs\AuthYouTube;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iCanUpload;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Support\PlatformRegistry;
use InvalidArgumentException;

enum Platform: string
{
    case VidGaze = 'vidgaze';
    case YouTube = 'youtube';
    case Dailymotion = 'dailymotion';
    case Vimeo = 'vimeo';
    case Twitch = 'twitch';
    case Rumble = 'rumble';
    case FaceBook = 'facebook';

    case Odysee = 'odysee';
    case Podcasts = 'podcasts';
    case SoundCloud = 'soundcloud';
    case Spotify = 'spotify';
    case Instagram = 'instagram';
    case TikTok = 'tiktok';
    case BitChute = 'bitchute';

    public static function fromValue(string $value): Platform
    {
        return match ($value) {
            'vidgaze' => self::VidGaze,
            'youtube' => self::YouTube,
            'dailymotion' => self::Dailymotion,
            'vimeo' => self::Vimeo,
            'twitch' => self::Twitch,
            'rumble' => self::Rumble,
            'odysee' => self::Odysee,
            'podcasts' => self::Podcasts,
            'soundcloud' => self::SoundCloud,
            'spotify' => self::Spotify,
            'instagram' => self::Instagram,
            'facebook' => self::FaceBook,
            'tiktok' => self::TikTok,
            'bitchute' => self::BitChute,
            default => throw new InvalidArgumentException('Invalid value for Platform'),
        };
    }

    function getPrefix(): string
    {
        return match ($this) {
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
            Platform::FaceBook => 'fb',
            Platform::TikTok => 'tt',
            Platform::BitChute => 'bc',
        };
    }

    function getCategoryIdAttribute(): string
    {
        return match ($this) {
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
            Platform::FaceBook => 'facebook_category_id',
            Platform::TikTok => 'tiktok_category_id',
            Platform::BitChute => 'bitchute_category_id',
        };
    }

    public function jsonSerialize(): string
    {
        return serialize($this);
    }

    public static function getSupportedPlatforms(bool $asEnum = false, bool $asPrefix = false): \Illuminate\Support\Collection
    {
        return PlatformRegistry::supportedForVideoIndex($asEnum, $asPrefix);
    }

    function getPlatformClass(): iIsPlatform
    {
        $class = PlatformRegistry::platformApiClass($this);
        if ($class === null || ! is_a($class, iIsPlatform::class, true)) {
            throw new InvalidArgumentException("No platform API implementation registered for {$this->value}");
        }

        return new $class;
    }

    function getPlatformAuthObject($accessToken): iIsPlatform | iCanUpload
    {
        return match ($this) {
            Platform::YouTube => new AuthYouTube($accessToken),
            Platform::Vimeo => new AuthVimeo($accessToken),
            Platform::Twitch => new AuthTwitch($accessToken),
            Platform::Dailymotion => new AuthDailymotion($accessToken),
            default => throw new InvalidArgumentException("No authenticated API object for {$this->value}"),
        };
    }

    function getPlatformAuthClass(): string
    {
        $class = PlatformRegistry::authClassForLogin($this);
        if ($class === null) {
            throw new InvalidArgumentException("Platform {$this->value} does not support OAuth login");
        }

        return $class;
    }

    public static function getUploadablePlatforms(bool $asEnum = true, bool $asPrefix = false): \Illuminate\Support\Collection
    {
        return PlatformRegistry::uploadable($asEnum, $asPrefix);
    }
}
