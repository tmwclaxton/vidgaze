<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class AuthFlagCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check if header has auth_flag cookie set to true
        if (request()->cookie('auth_flag') === null || request()->cookie('auth_flag') !== 'true') {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
