<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;


class PodcastController extends Controller
{

    public function index()
    {


        return Inertia::render('Viewer/Podcasts/PodcastsIndex');

    }

    public function infinite(Request $request) {
        $perPage = $request->perPage ?? 20;
        //get ids from params
        $podcastIds = $request->podcastIds ?? [];
        // Get the selected category
        $selectedCategory = $request->input('category') ?? 'popular';


        if (!is_array($podcastIds) ) {
            //explode the ids into an array
            $podcastIds = explode(',', $podcastIds);
        }

        //get the podcasts
        $query = Podcast::query();
        if ($selectedCategory == 'new') {
            $query->orderByDesc('time_published');
        } elseif ($selectedCategory == 'random') {
            $query->inRandomOrder();
        }

        // Only get public podcasts
        $query->where('visibility', '=','public');


        // Only get podcasts that are not in the array of ids
        $query->whereNotIn('id', $podcastIds);

        // Get the podcasts
        $podcasts = $query->paginate($perPage);

        // Return the podcasts
        return $podcasts;


    }


    public function create()
    {
        //
    }


    public function store()
    {

    }


    public function show(Podcast $podcast) {
        return view('podcasts.podcast');
    }
    public function episode(Podcast $podcast) {
        return view('podcasts.episode',['video'=>Video::take(1)->first()]);
    }


    public function edit(Podcast $podcast)
    {
//        return view('studio.stream', [
//            'item'=> $stream,
//        ]);
    }


    public function update()
    {

    }


    public function destroy()
    {
        //
    }
}
