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

class VideoWebController extends Controller
{

    public function show(string $slug)
    {
        return Inertia::render('Viewer/Watch/Watch', [
            'type' => 'video',
            'slug' => $slug,
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
        return Inertia::render('Viewer/Shorts/ShortsIndex');
    }

}
