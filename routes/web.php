<?php

use App\Http\Controllers\Content\CategoryController;
use App\Http\Controllers\Content\MusicController;
use App\Http\Controllers\Content\PodcastController;
use App\Http\Controllers\Content\ShareController;
use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Search\SearchController;
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

// landing route
Route::get('/', function () {
    if (auth()->user() === null) {
        return redirect()->route('about');
    } else {
        return redirect()->route('home');
    }
})->name('landing');


//home route
Route::get('/home', function () {
    return Inertia::render('Viewer/Home/Homepage');
})->name('home');

require __DIR__ . '/ContentRoutes/videos.php';
require __DIR__ . '/ContentRoutes/podcasts.php';
require __DIR__ . '/ContentRoutes/streams.php';
require __DIR__ . '/ContentRoutes/channels.php';
require __DIR__ . '/ContentRoutes/studio.php';
require __DIR__ . '/ContentRoutes/playlists.php';
require __DIR__.'/auth.php';


//category routes
Route::get('category/{category:slug}', [CategoryController::class,'show'])->name("category.show");
Route::get('categories', [CategoryController::class,'index'])->name("categories.index"); //used by carousel on stream page
Route::get('categories/infinite', [CategoryController::class, 'infinite'])->name('categories.infinite');



//music routes
Route::get('/music', [MusicController::class,'index'])->name('music.index');
//Route::get('/music/album', [MusicController::class,'album'])->name('music.album');
//Route::get('/music/category', [MusicController::class,'category'])->name('music.category');
//Route::get('/music/track', [MusicController::class,'track'])->name('music.track');




//this is the route for creating share links
Route::get('/shares', [ShareController::class, 'index'])->name('share.index');
// limit to 5 requests per 15 minutes





//search routes
Route::get('/search/', [SearchController::class, 'get']);
Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchBarController::class, 'get'])->name('search_suggestions');
});


//about
Route::get('/about', function () { return Inertia::render('Viewer/Landing');})->name('about');

//policy and terms
Route::get('/terms_of_service', function () { return Inertia::render('Legal/Terms'); })->name('terms');
Route::get('/privacy_policy', function () { return Inertia::render('Legal/Policy'); })->name('privacy');



