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
//        dd(Twitch::getCategories(null, true));
//        dd(Twitch::getTopStreamsByCategory(33214));

        ddd(Twitch::updateTopCategories(3, 3));

        dd("hi");
        dd(Carbon::create(null));

//        ddd(Carbon::create("2022-12-27T16:33:35.000000Z")->toDate());
//        $creator = Creator::where('slug', 'yt_UC-lHJZR3Gqxm24_Vd_AJ5Yw')->firstOrFail();
//        $isGhostChannel = $creator->user()->first() === null;

//        dd($isGhostChannel);
//        ddd($creator);
//        $video_titles = $creator->videos()->orderBy('time_published', 'desc')->pluck('title');
//        ddd($video_titles);
//        $video_published_times = $creator->videos()->orderBy('time_published', 'desc')->pluck('time_published');

//        ddd($video_published_times, $video_titles);


        $dm_vids = YouTube::getCreatorVideosBeforeDate('UC-lHJZR3Gqxm24_Vd_AJ5Yw', null, 3);
        ddd($dm_vids);
//        ddd($yt_vids, $video_titles, $video_published_times, $creator);

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
