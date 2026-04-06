<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PodcastCollection;
use App\Http\Resources\PodcastEpisodeResource;
use App\Http\Resources\PodcastResource;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastModels\Podcast;
use Illuminate\Http\Request;

class PodcastApiController extends Controller
{
    public function index(Request $request): PodcastCollection
    {
        $perPage = min(50, max(1, (int) ($request->input('perPage', 20))));
        $podcastIds = $request->input('podcastIds', []);
        if (! is_array($podcastIds)) {
            $podcastIds = array_filter(array_map('trim', explode(',', (string) $podcastIds)));
        }
        $selectedCategory = $request->input('category') ?? 'popular';

        $query = Podcast::query()
            ->where('visibility', '=', 'public')
            ->with('creator');

        if ($podcastIds !== []) {
            $query->whereNotIn('id', $podcastIds);
        }

        if ($selectedCategory === 'new') {
            $query->orderByDesc('created_at');
        } elseif ($selectedCategory === 'random') {
            $query->inRandomOrder();
        } else {
            $query->orderByDesc('view_count');
        }

        return PodcastCollection::make($query->paginate($perPage));
    }

    public function show(string $slug): PodcastResource
    {
        $podcast = Podcast::query()->where('slug', $slug)->with('creator')->firstOrFail();

        return PodcastResource::make($podcast);
    }

    public function episode(string $podcastSlug, string $episodeSlug): PodcastEpisodeResource
    {
        $podcast = Podcast::query()->where('slug', $podcastSlug)->firstOrFail();
        $episode = PodcastEpisode::query()
            ->where('podcast_id', $podcast->id)
            ->where('slug', $episodeSlug)
            ->with('podcast')
            ->firstOrFail();

        return PodcastEpisodeResource::make($episode);
    }
}
