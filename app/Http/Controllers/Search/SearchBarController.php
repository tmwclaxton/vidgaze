<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Http\Request;

class SearchBarController extends Controller
{

    public function get(Request $request)
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
