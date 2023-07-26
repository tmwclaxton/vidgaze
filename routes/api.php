<?php

use App\Helpers\JoshPing;
use App\Http\Controllers\ApiControllers\ShareApiController;
use App\Http\Controllers\ApiControllers\ViewListenerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

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

Route::prefix('v1')->name('v1')->group(function () {

    //this is a test route to make sure the api is working
    Route::get('health', function () {
        // check if the database is connected
        $database = DB::connection()->getPdo() ? 'CONNECTED' : 'NOT CONNECTED';
        // check if redis is connected
        $redis = Redis::connection()->ping() ? 'CONNECTED' : 'NOT CONNECTED';
        // check what storage driver is being used
        $storage = config('filesystems.default');


        // if local storage check this is not a production server assuming we are using s3 in production
        if ($database == 'CONNECTED' && $redis == 'CONNECTED' && ($storage == 'local' && config('app.env') != 'production') ) {
            $message = 'VidGaze API v1 is working!';
        } else {
            $message = 'VidGaze API v1 is not working!';
        }

        return response()->json([
            'message' => $message,
            'database' => $database,
            'redis' => $redis,
            'filesystem' => $storage,
        ]);
    })->name('health');

    require __DIR__ . '/ApiV1Routes/videos.php';
    require __DIR__ . '/ApiV1Routes/comments.php';
    require __DIR__ . '/ApiV1Routes/podcasts.php';
    require __DIR__ . '/ApiV1Routes/streams.php';
    require __DIR__ . '/ApiV1Routes/creators.php';
    require __DIR__ . '/ApiV1Routes/studio.php';
    require __DIR__ . '/ApiV1Routes/feed.php';
    require __DIR__ . '/ApiV1Routes/categories.php';
    require __DIR__ . '/ApiV1Routes/search.php';
    require __DIR__ . '/ApiV1Routes/music.php';
    require __DIR__ . '/ApiV1Routes/auth.php';
    require __DIR__ . '/ApiV1Routes/user.php';
    require __DIR__ . '/ApiV1Routes/playlists.php';

    Route::get('/ping', function () {
        return JoshPing::ping();
    });

    //this is the route for creating share links
    Route::get('/shares', [ShareApiController::class, 'index'])->name('share.index');

    // view listener route
    Route::post('/view-listener', [ViewListenerController::class, 'message'])->middleware('auth.sanctum.switch')->name('view.listener');

});
