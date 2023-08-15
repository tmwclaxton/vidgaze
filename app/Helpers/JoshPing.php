<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
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
use Google_Service_YouTube;
use GuzzleHttp\Client;
use Laravel\Octane\Facades\Octane;

class JoshPing
{

    public static function ping()
    {
        // access bearer token from request
        $token = request()->bearerToken();
        return ['message' => 'success', 'token' => $token];

        // query local api route
        $client = new Client();
        $response = $client->get('http://localhost/api/v1/studio/link/youtube');
        dd($response->getBody()->getContents());
        return response()->json([
            'message' => 'pong',
            'time' => now(),
        ]);
        dd(auth()->user());
//        dd("hi");
//        dd(\Storage::get('public/thumbnails/m55NepKM9JDBsn7pQos1azpZXZIMGvolGvfAgBLn.jpg'));
        $creator = auth()->user()->creator()->first();
        dd($creator);
        $ayt = new AuthYouTube($creator->sources()->where('source_name', Platform::YouTube)->first()->refreshAccessToken());
        ddd($ayt->getMyCreator());
        dd(Visibility::PUBLIC->value);

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
