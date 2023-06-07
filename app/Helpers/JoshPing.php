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
        $query = new SearchQueryDTO('chess', 5);

        $start = microtime(true);
        $octane = Octane::concurrently([
//           /* fn() =>*/ YouTube::search($query),
           fn() => Dailymotion::search($query),
           fn() => Vimeo::search($query),
           fn() => Twitch::search($query)
        ], 10000);
        $time = microtime(true) - $start;
        ddd($time, $octane);


        return dd('pong');
    }
}
