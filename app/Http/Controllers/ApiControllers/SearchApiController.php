<?php

namespace App\Http\Controllers\ApiControllers;

use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorCollection;
use App\Http\Resources\PlaylistCollection;
use App\Http\Resources\PodcastCollection;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\VideoCollection;
use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchApiController extends Controller
{

    /** Get search results for a query
     * @param Request $request
     */
    public static function startQuery(Request $request)
    {
        $searchQuery = $request->q;
        $query = new SearchQueryDTO($searchQuery, 10);
        Search::searchJobs($query);
    }

    /** Get search results for a query
     * @param Request $request
     * @return JsonResponse
     */
    public static function getResults(Request $request) {
        $searchQuery = $request->q;
        if (empty($searchQuery)) {
            return response()->json([
                'creators' => [],
                'videos' => [],
                'streams' => [],
                'playlists' => [],
                'podcasts' => [],
            ]);
        }

        $query = new SearchQueryDTO($searchQuery, 20);

        $results = Search::searchResults($query);

        // return the creators, videos, streams, playlists, podcasts in a collection
        $creators = array_key_exists("creators", $results) ? new CreatorCollection($results['creators']) : [];
        $videos = array_key_exists("videos", $results) ? new VideoCollection($results['videos']) : [];
        $streams = array_key_exists("streams", $results) ? new StreamCollection($results['streams']) : [];
        $playlists = array_key_exists("playlists", $results) ? new PlaylistCollection($results['playlists']) : [];
        $podcasts = array_key_exists("podcasts", $results) ? new PodcastCollection($results['podcasts']) : [];
        // shuffle videos in a repeatable way // we need to shuffle for YouTube API approval
//        if (count($videos) > 0) {
//            $videos = $videos->shuffle(crc32($searchQuery));
//        }

        // pattern for videos: 2 yt vids, 3 rumble vids, 2 vimeos, 2 dailymotion, then shuffle the rest in a predictable way

        // iterate through videos and check their preferred_source
        $yt_vids = [];
        $rumble_vids = [];
        $vimeo_vids = [];
        $dailymotion_vids = [];
        $other_vids = [];

        foreach ($videos as $video) {
            if ($video->preferred_source == 'youtube' && count($yt_vids) < 2) {
                $yt_vids[] = $video;
            } elseif ($video->preferred_source == 'rumble' && count($rumble_vids) < 3) {
                $rumble_vids[] = $video;
            } elseif ($video->preferred_source == 'vimeo' && count($vimeo_vids) < 2) {
                $vimeo_vids[] = $video;
            } elseif ($video->preferred_source == 'dailymotion' && count($dailymotion_vids) < 2) {
                $dailymotion_vids[] = $video;
            } else {
                $other_vids[] = $video;
            }
        }


        // shuffle the rest of the videos in a predictable way
        $other_vids = collect($other_vids)->shuffle(crc32($searchQuery))->toArray();

        // merge all the videos
        $videos = collect(array_merge($yt_vids, $rumble_vids, $vimeo_vids, $dailymotion_vids, $other_vids));



        // hide important info from the user by using resource collections
        $creators = new CreatorCollection($creators);
        $videos = new VideoCollection($videos);
        $streams = new StreamCollection($streams);
        $playlists = new PlaylistCollection($playlists);
        $podcasts = new PodcastCollection($podcasts);

        // add 1 to impressions_count for each video in 1 query
        $video_ids = $videos->pluck('id');
        Video::whereIn('id', $video_ids)->increment('impressions_count');

        //return the creators, videos, streams, playlists, podcasts
        return response()->json([
            'creators' => $creators,
            'videos' => $videos,
            'streams' => $streams,
            'playlists' => $playlists,
            'podcasts' => $podcasts,
        ]);
    }


    /** Get search suggestions for a query
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearchSuggestions(Request $request)
    {
        $searchQuery = $request->q;

        if (empty($searchQuery) ) {
            return response()->json([
                'query' => $searchQuery,
                'videos' => [],
                'creators' => [],
                'playlists' => [],
                'podcasts' => [],
                'streams' => [],
                'categories' => [],
            ]);
        }

        //Ensure that search parameter is used to only display limited attributes
        $videos = Video::select(['slug','title'])->where('title','like','%'.$searchQuery.'%')->orderBy('view_count', 'DESC')->take(8)->get();
        $creators = Creator::select(['name','slug'])->where('name','like','%'.$searchQuery.'%')->orderByDesc('subscriber_count')->take(2)->get();
        $categories = Category::select(['name','slug'])->where('name','like','%'.$searchQuery.'%')->take(2)->get();
        return response()->json([
            'query' => $searchQuery,
            'videos' => $videos,
            'creators' => $creators,
            'playlists' => [],
            'podcasts' => [],
            'streams' => [],
            'categories' => $categories,

        ]);
    }
}
