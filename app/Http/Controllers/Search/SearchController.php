<?php

namespace App\Http\Controllers\Search;


use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;




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

        // hide important info from the user by using collection map


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
