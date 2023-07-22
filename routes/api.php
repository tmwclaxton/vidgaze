<?php

use App\Helpers\JoshPing;
use App\Http\Controllers\ApiControllers\ShareApiController;
use App\Http\Controllers\ApiControllers\ViewListenerController;
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

require __DIR__ . '/ApiRoutes/videos.php';
require __DIR__ . '/ApiRoutes/comments.php';
require __DIR__ . '/ApiRoutes/podcasts.php';
require __DIR__ . '/ApiRoutes/streams.php';
require __DIR__ . '/ApiRoutes/channels.php';
require __DIR__ . '/ApiRoutes/studio.php';
require __DIR__ . '/ApiRoutes/feed.php';
require __DIR__ . '/ApiRoutes/categories.php';
require __DIR__ . '/ApiRoutes/search.php';
require __DIR__ . '/ApiRoutes/music.php';

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::get('/ping', function(){
    return JoshPing::ping();
});


//this is the route for creating share links
Route::get('/shares', [ShareApiController::class, 'index'])->name('share.index');

// view listener route
Route::post('/view-listener', [ViewListenerController::class,'message'])->name('view.listener');
//Route::get('/view-listener', [ViewListenerController::class,'
