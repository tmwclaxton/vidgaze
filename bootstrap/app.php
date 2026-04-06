<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\AuthFlagCookie;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\Moderator;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SwitchGuard;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VidGazeAPI;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->replace(TrustProxies::class, \App\Http\Middleware\TrustProxies::class);
        $middleware->replace(TrimStrings::class, \App\Http\Middleware\TrimStrings::class);

        $middleware->web(
            append: [
                HandleInertiaRequests::class,
                AddLinkHeadersForPreloadedAssets::class,
            ],
            replace: [
                EncryptCookies::class => \App\Http\Middleware\EncryptCookies::class,
                PreventRequestForgery::class => \App\Http\Middleware\VerifyCsrfToken::class,
            ],
        );

        $middleware->statefulApi();

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.sanctum.switch' => SwitchGuard::class,
            'admin' => Admin::class,
            'moderator' => Moderator::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'vidgaze.api.key' => VidGazeAPI::class,
            'auth.flag.cookie' => AuthFlagCookie::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
