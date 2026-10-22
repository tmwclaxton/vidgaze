<?php

namespace App\Http\Controllers\WebControllers;

use App\Helpers\Seo;
use App\Http\Controllers\Controller;
use App\Support\Stats\PublicStats;
use Inertia\Inertia;
use Inertia\Response;

/** Public stats page. Aggregate counts only, cached for ten minutes. */
class StatsWebController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Stats/Stats', [
            'stats' => PublicStats::cached(),
            'meta' => Seo::make('Site stats', 'Live, anonymous numbers about VidGaze: videos, channels, platforms, categories, watches, searches and signups.'),
        ]);
    }
}
