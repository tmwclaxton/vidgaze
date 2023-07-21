<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Google;
use App\Helpers\PlatformAPIs\Podcasts;
use App\Helpers\PlatformAPIs\Spotify;
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
        $query = new SearchQueryDTO("jordan peterson", 5);
        $results = Search::search($query);

        dd($results);

//
//        $podcast = Spotify::search(new SearchQueryDTO(
//            request('search'),
//            1,
//        ))[0]->content;
//        return Spotify::exampleEmbed($podcast->id);



        ddd(Spotify::getPodcastEpisodes("50n0zRaHeMP5ubhXCP6DiD"));

        ddd(
            Spotify::getPodcasts([
                "3w4Kwemc2tQWz6VYMQKHtY",
                "32PFW6f3HVHqYXKKLPIMkf",
                "5jik76MGN7ncyTmAQqSEdn",
                "50n0zRaHeMP5ubhXCP6DiD",
                "2uuWPXRWILZxCFryQyuIA6"
            ])
        );


        ddd($results);
        return dd('pong');
    }
}
