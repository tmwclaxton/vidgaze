<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TusPhp\Tus\Server as TusServer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->singleton('tus-server', function ($app) {
//            $server = new TusServer('redis');
//
//            $server
//                ->setApiPath('/tus') // tus server endpoint.
//                ->setUploadDir(storage_path('app/public/uploads')); // uploads dir.
//
//            return $server;
//        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations from all subdirectories of the migrations directory
        $migrationsPath = database_path('migrations');
        $directories    = glob($migrationsPath.'/*', GLOB_ONLYDIR);
        $paths          = array_merge([$migrationsPath], $directories);

        $this->loadMigrationsFrom($paths);
    }
}
