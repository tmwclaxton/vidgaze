<?php

namespace App\Helpers;

use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping(): int
    {
        $query = new SearchQueryDTO('jordan peterson', 5);

        $start = microtime(true);
//        $results = Twitch::search($query);
//        $results = Search::search($query, 5);
//
//        ddd($results, microtime(true) - $start);

        $octane = Octane::concurrently([
//           /* fn() =>*/ YouTube::search($query),
           fn() => Dailymotion::search($query),
           fn() => Vimeo::search($query),
           fn() => Twitch::search($query)
        ], 10000);
        $octane[] = YouTube::search($query);
        $time = microtime(true) - $start;
        ddd($time, $octane);


        $start = microtime(true);
        $results=[];
        $results[] = YouTube::search($query);
        $results[] = Dailymotion::search($query);
        $results[] = Vimeo::search($query);
        $results[] = Twitch::search($query);
        $time = microtime(true) - $start;
        ddd($time, $results);

        return dd('pong');
    }
}
