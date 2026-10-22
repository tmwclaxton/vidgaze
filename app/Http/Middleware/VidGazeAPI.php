<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class VidGazeAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if header has config cron key
        $expected = (string) Config::get('app.vidgaze_api_key');
        $given = (string) $request->header('VIDGAZE_API_KEY');
        if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
