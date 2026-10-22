<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Controllers\Controller;
use App\Models\SiteEvent;
use App\Support\Stats\SiteEvents;
use Illuminate\Http\Request;

class SearchWebController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = $request->q;
        if (is_string($searchQuery) && trim($searchQuery) !== '' && !$request->header('X-Inertia-Partial-Data')) {
            SiteEvents::record(SiteEvent::SEARCH, 'search', term: SiteEvents::cleanTerm($searchQuery), request: $request);
        }
        return inertia('Viewer/Search/Search', [
            'searchQuery' => $searchQuery,
            'meta' => \App\Helpers\Seo::make($searchQuery ? "Search: {$searchQuery}" : 'Search'),
        ]);
    }

}
