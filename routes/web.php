<?php

use App\Http\Controllers\Content\CategoryController;
use App\Http\Controllers\Content\MusicController;
use App\Http\Controllers\Content\PodcastController;
use App\Http\Controllers\Content\ShareController;
use App\Http\Controllers\Content\SupportController;
use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Tools\ViewListenerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/ContentRoutes/videos.php';
require __DIR__ . '/ContentRoutes/comments.php';
require __DIR__ . '/ContentRoutes/podcasts.php';
require __DIR__ . '/ContentRoutes/streams.php';
require __DIR__ . '/ContentRoutes/channels.php';
require __DIR__ . '/ContentRoutes/studio.php';
require __DIR__ . '/ContentRoutes/feed.php';
require __DIR__ . '/ContentRoutes/categories.php';
require __DIR__ . '/ContentRoutes/search.php';
require __DIR__ . '/ContentRoutes/music.php';
require __DIR__.'/auth.php';

//this is the route for creating share links
Route::get('/shares', [ShareController::class, 'index'])->name('share.index');

// view listener route
Route::post('/view-listener', [ViewListenerController::class,'message'])->name('view.listener');
//Route::get('/view-listener', [ViewListenerController::class,'



// landing route
Route::get('/', function () {
    if (auth()->user() === null) {
        return redirect()->route('about');
    } else {
        return redirect()->route('home');
    }
})->name('landing');

//home route
Route::get('/home', [SupportController::class,'home'])->name('home');
//landing route
Route::get('/about', [SupportController::class, 'about'])->name('about');
//policy and terms
Route::get('/terms_of_service', [SupportController::class, 'terms'])->name('terms');
Route::get('/privacy_policy', [SupportController::class,'privacy'])->name('privacy');

//Route::get('/support', [SupportController::class,'support'])->name('support');
// support email route
//Route::post('/support', [SupportController::class,'sendSupportEmail'])->name('support.email.send');




Route::get('/ping', function(){
    return \App\Helpers\JoshPing::ping();
});


