<?php

namespace App\Http\Middleware;

use App\Models\SiteEvent;
use App\Support\Stats\SiteEvents;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Counts successful page views (full loads and Inertia visits) by route name only. */
class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ($request->header('X-Inertia') || str_contains((string) $response->headers->get('Content-Type'), 'text/html'))
            && !$request->header('X-Inertia-Partial-Data')
            && !$request->prefetch()) {
            SiteEvents::record(SiteEvent::PAGE_VIEW, $request->route()?->getName() ?? 'other', request: $request);
        }

        return $response;
    }
}
