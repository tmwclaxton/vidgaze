<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
use App\Helpers\PlatformAPIs\AuthVimeo;
use App\Helpers\PlatformAPIs\AuthYouTube;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Google;
use App\Helpers\PlatformAPIs\Podcasts;
use App\Helpers\PlatformAPIs\Spotify;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use App\Models\VideoModels\Video;
use Carbon\Carbon;
use Google_Service_YouTube;
use GuzzleHttp\Client;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping()
    {

        $time = Video::where('slug', 'hpE4bbYdsS4HsGHy')->first()->time_published;
        dd($time);
        dd(Carbon::create($time)->format('j M Y'));
        $access_token = auth()->user()->creator()->first()->sources()->where('source_name', Platform::Vimeo)->first()->access_token;
        $v = new AuthVimeo($access_token);
        // Include the tags as a JSON array as the body of the request with the name field, like this: [{ "name": "funny"}, {"name": "concert" }]

        $thumbnail_path = 'public/thumbnails/xTkmSv3xdpQr8NKsp9kB7SX1tTwoVUX6vatAhcxp.jpg';
        $response = $v->setThumbnail('855459884', $thumbnail_path);
        return [$response];
//        $creator = auth()->user()->creator()->first();
//
//        $yt = new YouTube(null, $creator->sources()->where('source_name', Platform::YouTube)->first()->access_token);
//        $yt_channel_id = $yt->getMyCreator()->id;

//        dd($yt_channel_id);

//        $podcast = Spotify::search(new SearchQueryDTO(
//            request('search'),
//            1,
//        ))[0]->content;
//        return Spotify::exampleEmbed($podcast->id);
//


//        ddd(Spotify::getPodcastEpisodes("50n0zRaHeMP5ubhXCP6DiD"));

//        ddd(
//            Spotify::getPodcasts([
//                "3w4Kwemc2tQWz6VYMQKHtY",
//                "32PFW6f3HVHqYXKKLPIMkf",
//                "5jik76MGN7ncyTmAQqSEdn",
//                "50n0zRaHeMP5ubhXCP6DiD",
//                "2uuWPXRWILZxCFryQyuIA6"
//            ])
//        );


//        ddd($results);
        return dd('pong');
    }
}
