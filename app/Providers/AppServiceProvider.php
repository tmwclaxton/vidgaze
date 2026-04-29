<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
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
        // Load migrations from the main tree, subdirectories, and database/patches (categories, etc.)
        $migrationsPath = database_path('migrations');
        $patchesPath = database_path('patches');
        $directories = glob($migrationsPath.'/*', GLOB_ONLYDIR) ?: [];
        $paths = array_merge([$migrationsPath], $directories);
        if (is_dir($patchesPath)) {
            $paths[] = $patchesPath;
        }

        JsonResource::withoutWrapping(); // this is so that the json response does not have a data wrapper

        $this->loadMigrationsFrom($paths);

        // if ($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }
    }
}
