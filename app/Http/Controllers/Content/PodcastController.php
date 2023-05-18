<?php

namespace App\Http\Controllers\Content;


use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\Video;
use Inertia\Inertia;


class PodcastController extends Controller
{

    public function index()
    {


        return Inertia::render('Viewer/Podcasts/PodcastsIndex');

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
