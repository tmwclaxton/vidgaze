<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthFlagCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->cookie('auth_flag') === 'true') {
            return $next($request);
        }

        $request->session()->flash('show_auth_modal', true);
        $request->session()->flash('auth_intended_url', $request->fullUrl());

        if ($request->header('X-Inertia')) {
            return redirect()->back(fallback: route('home'));
        }

        return redirect()->route('home');
    }
}
