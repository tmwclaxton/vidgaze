<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\VideoCollection;
use App\Http\Resources\VideoResource;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\SiteEvent;
use App\Support\Stats\SiteEvents;

class VideoWebController extends Controller
{

    public function show(string $slug)
    {
        $videoId = Video::where('slug', $slug)->value('id');
        if ($videoId) {
            SiteEvents::record(SiteEvent::WATCH, 'video', (int) $videoId);
        }
        return Inertia::render('Viewer/Watch/Watch', [
            'type' => 'video',
            'slug' => $slug,
            'meta' => \App\Helpers\Seo::video($slug),
        ]);
    }

    public function edit(string $slug)
    {
        return Inertia::render('Studio/StudioEditItem/StudioEditItem', [
            'slug' => $slug,
            'type' => 'video'
        ]);
    }

    public function shorts()
    {
        $short = request('short');
        if ($short && ($videoId = Video::where('slug', $short)->value('id'))) {
            SiteEvents::record(SiteEvent::WATCH, 'short', (int) $videoId);
        }
        $meta = $short ? \App\Helpers\Seo::video($short) : \App\Helpers\Seo::make('Shorts', 'Short videos from YouTube, Rumble and more, in one feed on VidGaze.');
        return Inertia::render('Viewer/Shorts/ShortsIndex', ['meta' => $meta]);
    }

}
