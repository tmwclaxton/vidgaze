<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints get their own named buckets. A bare throttle:N,1 keys every
        // route on the same domain|ip signature, so ordinary guest browsing used up the
        // register and login allowance and visitors got 429s when signing up or in.
        $authLimits = [
            'auth-register' => 5,
            'auth-login' => 10,
            'auth-password' => 5,
            'auth-verify-send' => 5,
            'auth-sensitive' => 10,
        ];
        foreach ($authLimits as $name => $perMinute) {
            RateLimiter::for($name, function (Request $request) use ($perMinute) {
                return Limit::perMinute($perMinute)->by($request->user()?->id ?: $request->ip());
            });
        }
    }
}
