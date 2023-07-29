<?php

namespace App\Http\Controllers\WebControllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastCollection;
use App\Models\PodcastModels\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class PodcastWebController extends Controller
{

    public function index()
    {
        return Inertia::render('Viewer/Podcasts/PodcastsIndex');
    }

}
