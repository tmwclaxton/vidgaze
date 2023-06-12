<?php

namespace App\Http\Controllers\Search;


use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Http\Resources\PlaylistCollection;
use App\Http\Resources\PodcastCollection;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\VideoCollection;
use Illuminate\Http\Request;




class SearchController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->q;
        return inertia('Viewer/Search/Search', [
            'searchQuery' => $searchQuery,
        ]);
    }

    public static function get(Request $request)
    {
        $searchQuery = $request->q;
        if (empty($searchQuery)) {
           return [];
        }

        $query = new SearchQueryDTO($searchQuery, 5);

        $start = microtime(true);
        $results = Search::search($query, );

        // return the creators, videos, streams, playlists, podcasts in a collection
        //$creators = if $results['creators'] is not null then new CreatorCollection($results['creators']) otherwise []
        $creators = array_key_exists("creators",$results) ? new CreatorCollection($results['creators']) : [];
        $videos = array_key_exists("videos",$results) ? new VideoCollection($results['videos']) : [];
        $streams = array_key_exists("streams",$results) ? new StreamCollection($results['streams']) : [];
        $playlists = array_key_exists("playlists",$results) ? new PlaylistCollection($results['playlists']) : [];
        $podcasts = array_key_exists("podcasts",$results) ? new PodcastCollection($results['podcasts']) : [];


        // hide important info from the user by using collection map

        //return the creators, videos, streams, playlists, podcasts
        return [
            'creators' => $creators,
            // shuffle videos
            'videos' => $videos,
            'streams' => $streams,
            'playlists' => $playlists,
            'podcasts' => $podcasts,
        ];
    }
}
