<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class CronKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if header has config cron key
        if (request()->header('CRON_KEY') === null || request()->header('CRON_KEY') !== Config::get('app.cron_key')) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
