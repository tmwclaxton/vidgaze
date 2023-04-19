<?php

namespace App\Http\Controllers\Api;

use App\Enums\Platforms;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Helpers\SearchResultDTO;
use App\Http\Controllers\Controller;
use App\Services\MixPanelTrackingService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Laravel\Octane\Exceptions\TaskException;
use Laravel\Octane\Exceptions\TaskTimeoutException;
use Laravel\Octane\Facades\Octane;


class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->q;
        return inertia('Search/Search', [
            'searchQuery' => $searchQuery,
        ]);
    }

    public static function get(Request $request)
    {
        $maxResults = 3;

        $searchQuery = $request->q;
        if (empty($searchQuery)) {
           return [];
        }
        //add mixpanel tracking here later on

        // Redis::client()->flushAll();

        $query_dto = new SearchQueryDTO($searchQuery,  $maxResults);
        $searchResults = Search::octaneSearch($query_dto);

        $creators = $searchResults['creators'];
        $videos = $searchResults['videos'];
        $streams = $searchResults['streams'];
        $playlists = $searchResults['playlists'];
        $podcasts = $searchResults['podcasts'];

        //grab only specific fields i.e. name, subscriber count from the creators array
        // hide important fields from the creators array
        $creators = array_map(function($creator) {
            return [
                'name' => $creator['name'],
                'subscriber_count' => $creator['subscriber_count'],
                'avatar_url' => $creator['avatar_url'],
                'slug' => $creator['slug'],
                'is_live' => $creator['is_live'],
                'sources' => $creator['sources'],
            ];
        }, $creators);

        //hide important fields from the videos array
        $videos = array_map(function($video) {
            return [
                'title' => $video['title'],
                'thumbnail_url' => $video['thumbnail_url'],
                'view_count' => $video['view_count'],
                'duration' => $video['duration'],

                'slug' => $video['slug'],
                'creator.name' => $video['creator']['name'],
                'creator.avatar_url' => $video['creator']['avatar_url'],
                'creator.slug' => $video['creator']['slug'],
            ];
        }, $videos);

        //return the creators, videos, streams, playlists, podcasts
        return [
            'creators' => $creators,
            'videos' => $videos,
            'streams' => $streams,
            'playlists' => $playlists,
            'podcasts' => $podcasts,
        ];
    }
}
