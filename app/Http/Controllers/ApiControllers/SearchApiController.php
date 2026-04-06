<?php

namespace App\Http\Controllers\ApiControllers;

use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Helpers\SearchVideoAiRanker;
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
     */
    public static function startQuery(Request $request)
    {
        $searchQuery = $request->q;
        $query = new SearchQueryDTO($searchQuery, 10);
        Search::searchJobs($query);
    }

    /** Get search results for a query
     * @return JsonResponse
     */
    public static function getResults(Request $request)
    {
        $searchQuery = $request->q;
        if (empty($searchQuery)) {
            return response()->json([
                'creators' => [],
                'videos' => [],
                'streams' => [],
                'playlists' => [],
                'podcasts' => [],
                'video_ranking' => null,
            ]);
        }

        $query = new SearchQueryDTO($searchQuery, 20);

        $results = Search::searchResults($query);

        $creatorModels = $results['creators'] ?? [];
        $videoModels = $results['videos'] ?? [];
        $streamModels = $results['streams'] ?? [];
        $playlistModels = $results['playlists'] ?? [];
        $podcastModels = $results['podcasts'] ?? [];
        // shuffle videos in a repeatable way // we need to shuffle for YouTube API approval
        //        if (count($videos) > 0) {
        //            $videos = $videos->shuffle(crc32($searchQuery));
        //        }

        // Seed ordering: platform quotas first, remainder in discovery order; final order from AI + Redis cache.
        $yt_vids = [];
        $rumble_vids = [];
        $vimeo_vids = [];
        $dailymotion_vids = [];
        $bitchute_vids = [];
        $odysee_vids = [];
        $other_vids = [];

        foreach ($videoModels as $video) {
            if ($video->preferred_source == 'youtube' && count($yt_vids) < 2) {
                $yt_vids[] = $video;
            } elseif ($video->preferred_source == 'rumble' && count($rumble_vids) < 3) {
                $rumble_vids[] = $video;
            } elseif ($video->preferred_source == 'vimeo' && count($vimeo_vids) < 2) {
                $vimeo_vids[] = $video;
            } elseif ($video->preferred_source == 'dailymotion' && count($dailymotion_vids) < 2) {
                $dailymotion_vids[] = $video;
            } elseif ($video->preferred_source == 'bitchute' && count($bitchute_vids) < 2) {
                $bitchute_vids[] = $video;
            } elseif ($video->preferred_source == 'odysee' && count($odysee_vids) < 2) {
                $odysee_vids[] = $video;
            } else {
                $other_vids[] = $video;
            }
        }

        $videoModels = array_merge($yt_vids, $rumble_vids, $vimeo_vids, $dailymotion_vids, $bitchute_vids, $odysee_vids, $other_vids);

        [$videoModels, $videoRankingMeta] = SearchVideoAiRanker::rankVideos($videoModels, $searchQuery);

        $videoIds = collect($videoModels)->pluck('id')->filter()->values();
        if ($videoIds->isNotEmpty()) {
            Video::whereIn('id', $videoIds)->increment('impressions_count');
        }

        $streams = new StreamCollection($streamModels);
        $playlists = new PlaylistCollection($playlistModels);
        $podcasts = new PodcastCollection($podcastModels);

        // return the creators, videos, streams, playlists, podcasts
        return response()->json([
            'creators' => new CreatorCollection(collect($creatorModels)),
            'videos' => new VideoCollection(collect($videoModels)),
            'streams' => $streams,
            'playlists' => $playlists,
            'podcasts' => $podcasts,
            'video_ranking' => $videoRankingMeta,
        ]);
    }

    /** Get search suggestions for a query
     * @return JsonResponse
     */
    public function getSearchSuggestions(Request $request)
    {
        $searchQuery = $request->q;

        if (empty($searchQuery)) {
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

        // Ensure that search parameter is used to only display limited attributes
        $videos = Video::select(['slug', 'title'])->where('title', 'like', '%'.$searchQuery.'%')->orderBy('view_count', 'DESC')->take(8)->get();
        $creators = Creator::select(['name', 'slug'])->where('name', 'like', '%'.$searchQuery.'%')->orderByDesc('subscriber_count')->take(2)->get();
        $categories = Category::select(['name', 'slug'])->where('name', 'like', '%'.$searchQuery.'%')->take(2)->get();

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
