<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwitchGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @param string $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // this should check if there is a bearer token in the request in which case it should use the sanctum otherwise it should use the web guard
        if ($request->bearerToken()) {
            Auth::shouldUse('sanctum');
        } else {
            Auth::shouldUse('web');
        }

        return $next($request);
    }
}
