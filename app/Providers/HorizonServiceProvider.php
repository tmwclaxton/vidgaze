<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
         Horizon::routeMailNotificationsTo('tmwclaxton@gmail.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');

         Horizon::night();
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        $adminEmails = config('admins.emails');
        Gate::define('viewHorizon', function ($user) use ($adminEmails) {
            return in_array($user->email, $adminEmails);
        });
    }
}
