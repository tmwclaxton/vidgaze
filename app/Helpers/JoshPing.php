<?php

namespace App\Helpers;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping(): int
    {
        $query = new SearchQueryDTO('jordan peterson', 25);

        $start = microtime(true);
        $results = Search::search($query, 5);

        ddd($results, microtime(true) - $start);
////
//        $octane = Octane::concurrently([
////           /* fn() =>*/ YouTube::search($query),
//           fn() => Dailymotion::search($query),
//           fn() => Vimeo::search($query),
//           fn() => Twitch::search($query)
//        ], 10000);
//        $octane[] = YouTube::search($query);
//        $time = microtime(true) - $start;
//        ddd($time, $octane);



        $start = microtime(true);
//        $results=[];
//        foreach (Platform::getSupportedPlatforms(true)->toArray() as $platform) {
//            $results[] = $platform->getPlatformClass()::search($query);
//        }

        $start_2 = microtime(true);
        $results['yt'] = [YouTube::search($query), microtime(true) - $start_2];
        $start_2 = microtime(true);
        $results['dm'] = [Dailymotion::search($query), microtime(true) - $start_2];
        $start_2 = microtime(true);
        $results['vm'] = [Vimeo::search($query), microtime(true) - $start_2];
        $start_2 = microtime(true);
        $results['tw'] = [Twitch::search($query), microtime(true) - $start_2];
        $time = microtime(true) - $start;
        ddd($time, $results);

        return dd('pong');
    }
}
