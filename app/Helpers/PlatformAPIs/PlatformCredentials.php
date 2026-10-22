<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Platform;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Twitch, Vimeo and Dailymotion need API credentials. When they are not set the
 * SDK constructors throw, which used to fail search jobs, scheduled commands and
 * API calls every few minutes. Callers check configured() first and skip.
 */
class PlatformCredentials
{
    /** config keys that must all be filled for each platform */
    private const REQUIRED = [
        'Twitch' => ['platforms.twitch.client_id', 'platforms.twitch.client_secret'],
        'Vimeo' => ['platforms.vimeo.client_id', 'platforms.vimeo.client_secret'],
        'Dailymotion' => ['platforms.dailymotion.client_key', 'platforms.dailymotion.client_secret'],
    ];

    public static function configured(Platform $platform): bool
    {
        $keys = self::REQUIRED[$platform->name] ?? [];
        foreach ($keys as $key) {
            if (blank(config($key))) {
                return false;
            }
        }
        return true;
    }

    /**
     * True when the platform can be used. When it cannot, log one warning per
     * platform per day (not one per call) and return false so the caller skips.
     */
    public static function ensure(Platform $platform, string $context = ''): bool
    {
        if (self::configured($platform)) {
            return true;
        }

        $cacheKey = 'platform-unconfigured-warned:' . $platform->name;
        try {
            $first = Cache::add($cacheKey, true, now()->addDay());
        } catch (\Throwable) {
            $first = true;
        }
        if ($first) {
            Log::warning("{$platform->name} API credentials are not set, skipping {$platform->name} calls"
                . ($context ? " ({$context})" : '') . '. Set them in .env to enable it.');
        }
        return false;
    }

    /** @return Platform[] supported platforms that have credentials (or need none) */
    public static function usable(iterable $platforms): array
    {
        $out = [];
        foreach ($platforms as $platform) {
            if (!array_key_exists($platform->name, self::REQUIRED) || self::ensure($platform, 'search')) {
                $out[] = $platform;
            }
        }
        return $out;
    }
}
