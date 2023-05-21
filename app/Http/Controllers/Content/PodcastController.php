<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastCollection;
use App\Models\PodcastModels\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return new PodcastCollection($podcasts);


    }



    public function create()
    {
        //
    }


    public function store()
    {

    }


    public function show(Podcast $podcast) {

    }
    public function episode(Podcast $podcast) {

    }


    public function edit(Podcast $podcast)
    {

    }


    public function update()
    {

    }


    public function destroy()
    {
        //
    }
}
