<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Google;
use App\Helpers\PlatformAPIs\Podcasts;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Google_Service_YouTube;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping()
    {

        $creator = Creator::where('name', 'joshuasy10')->with('sources');
        $creator_source = $creator->first()->sources->first();
//        dd($creator_source->access_token);
//        dd($creator_source->refresh_token);

        $refreshed = YouTube::getRefreshAccessToken($creator_source->refresh_token);
        $creator_source->update(['access_token' => $refreshed['access_token'], 'refresh_token' => $refreshed['refresh_token']]);

        $yt = new YouTube(null, $creator_source->access_token);
        $yt_channel_id = $yt->getMyCreator()->id;
        dd($yt_channel_id);
        die();

//        dd(array_map(fn($audience) => $audience->value, Audience::getAll()));
        $query = new SearchQueryDTO('pwediepie', 5);
//        ddd(Podcasts::search($query));

//        ddd(YouTube::search($query));

        $start = microtime(true);
        $results = Search::search($query, );
        ddd($results, microtime(true) - $start);

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
