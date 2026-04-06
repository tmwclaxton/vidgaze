<?php

namespace App\Http\Controllers\WebControllers;

use App\Helpers\PlatformAPIs\Podcasts;
use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastEpisodeResource;
use App\Http\Resources\PodcastResource;
use App\Models\PodcastModels\Podcast;
use Inertia\Inertia;

class PodcastWebController extends Controller
{
    public function index()
    {
        return Inertia::render('Viewer/Podcasts/PodcastsIndex');
    }

    public function show(string $slug)
    {
        $podcast = Podcast::query()->where('slug', $slug)->with('creator')->firstOrFail();
        Podcasts::syncEpisodesFromRss($podcast);
        $podcast->loadMissing(['creator', 'episodes']);
        $episodes = $podcast->episodes()->orderByDesc('time_published')->limit(100)->get();

        return Inertia::render('Viewer/Podcasts/PodcastShow', [
            'podcast' => (new PodcastResource($podcast))->resolve(),
            'episodes' => PodcastEpisodeResource::collection($episodes)->resolve(),
        ]);
    }

    public function episode(string $podcastSlug, string $episodeSlug)
    {
        $podcast = Podcast::query()->where('slug', $podcastSlug)->with('creator')->firstOrFail();
        $episode = $podcast->episodes()->where('slug', $episodeSlug)->firstOrFail();
        $episode->load('podcast');

        return Inertia::render('Viewer/Podcasts/PodcastEpisodeShow', [
            'podcast' => (new PodcastResource($podcast))->resolve(),
            'episode' => (new PodcastEpisodeResource($episode))->resolve(),
        ]);
    }
}
