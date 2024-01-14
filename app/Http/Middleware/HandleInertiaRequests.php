<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     *
     */
    public function share(Request $request): array
    {

        // pages where display is different
        // landing and watch no sidebar
        // login and auth no layout
        // studio routes different sidebar

        $layoutDisplay = 'default';

        $listOfAuthRoutes = [
            'register',
            'login',
            'password.request',
            'password.reset',
            'verification.notice',
            'verification.verify',
            'password.confirm',
        ];

        if ($request->routeIs('about') || $request->routeIs('watch.*') ||  $request->routeIs('stream.*') || $request->routeIs('marketplace')) {
            $layoutDisplay = 'wide';
        } elseif (in_array($request->route()->getName(), $listOfAuthRoutes)) {
            $layoutDisplay = 'auth';
        } elseif ($request->routeIs('studio.*')) {
            $layoutDisplay = 'studio';
        }

        return array_merge(parent::share($request), [
            'layoutDisplay' => $layoutDisplay,
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'status' => fn () => $request->session()->get('status'),
                'toast' => session('toast')
                ],
        ]);
    }
}
