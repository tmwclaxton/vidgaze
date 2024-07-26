<?php

use App\Helpers\JoshPing;
use App\Http\Controllers\ApiControllers\ShareApiController;
use App\Http\Controllers\ApiControllers\ViewListenerController;

use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Upload\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/




    // wrap all routes in v1
    Route::prefix('v1')->name('api.')->group(function () {

        // I think we should only ever have 1 version of the auth stuff for security
        require __DIR__ . '/ApiV1Routes/auth.php';

        require __DIR__ . '/ApiV1Routes/videos.php';
        require __DIR__ . '/ApiV1Routes/comments.php';
        require __DIR__ . '/ApiV1Routes/podcasts.php';
        require __DIR__ . '/ApiV1Routes/streams.php';
        require __DIR__ . '/ApiV1Routes/creators.php';
        require __DIR__ . '/ApiV1Routes/studio.php';
        require __DIR__ . '/ApiV1Routes/feed.php';
        require __DIR__ . '/ApiV1Routes/categories.php';
        require __DIR__ . '/ApiV1Routes/search.php';
        //require __DIR__ . '/ApiV1Routes/music.php';
        require __DIR__ . '/ApiV1Routes/user.php';
        require __DIR__ . '/ApiV1Routes/playlists.php';
        require __DIR__ . '/ApiV1Routes/cron.php';
        require __DIR__ . '/ApiV1Routes/unions.php';

        //this is the route for creating share links
        Route::get('/shares', [ShareApiController::class, 'index'])->name('share.index');

        // view listener route
        Route::post('/view-listener', [ViewListenerController::class, 'message'])->middleware('auth.sanctum.switch')->name('view.listener');



        //this is a test route to make sure the api is working
        Route::get('/health', function () {
            try {
                $database = DB::connection()->getPdo() ? 'CONNECTED: ' . env('DB_HOST') . ':' . env('DB_PORT')
                    : 'NOT CONNECTED';
            } catch (\Exception $e) {
                $database = 'NOT CONNECTED';
            }
            // check if redis is connected
            try {
                $redis = Redis::client()->ping() ? 'CONNECTED: ' . env('REDIS_HOST') . ':' . env('REDIS_PORT')
                    : 'NOT CONNECTED';
            } catch (\Exception $e) {
                $redis = 'NOT CONNECTED';
            }
            // check what storage driver is being used
            $storage = config('filesystems.default');


            // if local storage check this is not a production server assuming we are using s3 in production
            if ($database != 'NOT CONNECTED' && $redis != 'NOT CONNECTED') {
                $message = 'VidGaze API v1 is working!';
            } else {
                $message = 'VidGaze API v1 is not working!';
            }


            //if redis is working then try to write to it and read from it
            if ($redis == 'CONNECTED: ' . env('REDIS_HOST') . ':' . env('REDIS_PORT')) {
                try {
                    Redis::client()->set('test', 'test');
                    $redisMessage = Redis::client()->get('test');
                    Redis::client()->del('test');
                } catch (\Exception $e) {
                    $redis = 'NOT CONNECTED';
                }
            }

            // Get worker logs //
            $logPath = storage_path('logs/worker.log');
            $log = file_get_contents($logPath);
            $logSegments = explode("\n", $log);
            $workerLogs = array_filter($logSegments, 'strlen');

            // Get laravel logs //
            $logPath = storage_path('logs/laravel.log');
            $log = file_get_contents($logPath);
            $logSegments = explode("\n", $log);
            $laravelLogs = array_filter($logSegments, 'strlen');

            $swooleLogPath = storage_path('logs/swoole_http.log');
            $log = file_get_contents($swooleLogPath);
            $logSegments = explode("\n", $log);
            $swooleLogs = array_filter($logSegments, 'strlen');

            // reverse the logs so the newest is at the top //
            $workerLogs = array_reverse($workerLogs);
            $laravelLogs = array_reverse($laravelLogs);
            $swooleLogs = array_reverse($swooleLogs);

            // trim the logs to 100 lines
            $workerLogs = array_slice($workerLogs, 0, 100);
            $laravelLogs = array_slice($laravelLogs, 0, 100);
            $swooleLogs = array_slice($swooleLogs, 0, 100);

            return response()->json([
                'message' => $message,
                'database' => $database,
                'redis' => $redis,
                'redisMessage' => $redisMessage ?? null,
                'filesystem' => $storage,
                'laravelLogs' => $laravelLogs,
                'workerLogs' => $workerLogs,
                'swooleLogs' => $swooleLogs
            ], 200);
        })->name('health');


        // if in local environment add the ping route it should not be in production and if it is then JoshPing needs updated as its in gitignore
        if (config('app.env') == 'local') {
            Route::get('/ping', function () {
                return JoshPing::ping();
            })->middleware('auth:sanctum')->name('ping');
        }
    });


