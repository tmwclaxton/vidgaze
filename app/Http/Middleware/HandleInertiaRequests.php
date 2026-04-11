<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

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
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
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

        if ($request->routeIs('about') || $request->routeIs('watch.*') || $request->routeIs('stream.*') || $request->routeIs('marketplace')) {
            $layoutDisplay = 'wide';
        } elseif (in_array($request->route()->getName(), $listOfAuthRoutes)) {
            $layoutDisplay = 'auth';
        } elseif ($request->routeIs('studio.*')) {
            $layoutDisplay = 'studio';
        }

        $siteUrl = rtrim((string) config('app.url'), '/');
        $og = (string) config('seo.default_og_image', '');
        $defaultOgAbsolute = $og !== '' && Str::startsWith($og, ['http://', 'https://'])
            ? $og
            : $siteUrl.'/'.ltrim($og !== '' ? $og : 'favicon.ico', '/');

        return array_merge(parent::share($request), [
            'auth_modal' => fn () => [
                'should_open' => (bool) $request->session()->pull('show_auth_modal', false),
                'intended_url' => $request->session()->pull('auth_intended_url'),
            ],
            'layoutDisplay' => $layoutDisplay,
            'seo' => [
                'siteName' => (string) config('app.name', 'VidGaze'),
                'siteUrl' => $siteUrl,
                'defaultDescription' => (string) config('seo.default_description', ''),
                'defaultOgImage' => $defaultOgAbsolute,
                'twitterSite' => config('seo.twitter_site') ? (string) config('seo.twitter_site') : null,
                'canonicalUrl' => $request->url(),
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'status' => fn () => $request->session()->get('status'),
                'toast' => session('toast'),
            ],
        ]);
    }
}
