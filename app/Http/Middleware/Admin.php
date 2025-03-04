<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //check if user is logged in
        if (!$request->user()) {
            return redirect()->route('login');
        }
        //check if user is admin
        $adminEmails = config('admins.emails');
        if (in_array($request->user()->email, $adminEmails) || $request->user()->role === 'admin') {
            // check if email is verified
            if (!$request->user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            return $next($request);
        } else {
            //redirect to home with error message
            return redirect()->route('home')->with('error', 'You are not authorised to view this page.');
        }
    }
}
