<?php

use App\Http\Controllers\Content\CategoryController;
use App\Http\Controllers\Content\ChannelDisinterestController;
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\PlaylistVideoController;
use App\Http\Controllers\Content\VideoDisinterestController;
use App\Http\Controllers\Search\SearchController;
use App\Http\Controllers\Content\MusicController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PodcastController;
use App\Http\Controllers\Content\ProfileController;
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\SubscriptionsController;
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Tools\ImportingController;
use App\Http\Controllers\Tools\LinkingController;
use App\Http\Controllers\Tools\UnionController;
use App\Http\Controllers\Tools\VideoUploadController;
use App\Http\Controllers\Tools\ViewListenerController;
use App\Models\Video;
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


//video routes
Route::get('/videos',[VideoController::class,'index'])->name('videos.index');
Route::get('/videos/infinite', [VideoController::class, 'infinite'])->name('videos.infinite');
Route::get('/shorts', [VideoController::class,'shorts'])->name('videos.shorts');
//view routes
Route::get('short/{video:slug}', [VideoController::class,'short'])->name("short.show");

Route::prefix('watch/{video:slug}')->name('watch.')->group(function () {
    Route::get('/', [VideoController::class, 'show'])->name('show');
    Route::get('{playlist:slug}', [VideoController::class, 'playlist'])->name('playlist');
    Route::get('{playlist:slug}/shuffle', [VideoController::class, 'shuffle'])->name('playlist.shuffle');
});

//channel routes
Route::get('channel/{creator:slug}', [CreatorController::class,'show'])->name("channel.show");

//stream routes
Route::get('/livestreams', [StreamController::class,'index'])->name("streams.index");
Route::get('/streams/top', [StreamController::class,'topStreams'])->name("streams.top");
Route::get('/stream/{stream:slug}', [StreamController::class,'show'])->name("stream.show");

//category routes
Route::get('category/{category:slug}', [CategoryController::class,'show'])->name("category.show");
Route::get('categories}', [CategoryController::class,'index'])->name("categories.index");


//podcast routes
Route::get('/podcasts', [PodCastController::class,'index'])->name("podcast.index");
Route::get('/podcast/', [PodCastController::class,'show'])->name("podcast.show");
Route::get('/podcast/episode/', [PodCastController::class,'episode'])->name("podcast.episode");

//music routes
Route::get('/music', [MusicController::class,'index'])->name('music.index');
Route::get('/music/album', [MusicController::class,'album'])->name('music.album');
Route::get('/music/category', [MusicController::class,'category'])->name('music.category');
Route::get('/music/track', [MusicController::class,'track'])->name('music.track');

//user feed routes
Route::middleware('auth')->group(function () {
    Route::get('/feed/library', [PlaylistController::class, 'index'])->name("feed.library");
    Route::post('/feed/playlist/{playlist:slug}', [PlaylistController::class, 'update'])->name("playlist.update");
    Route::get('/feed/watch_later', [PlaylistController::class, 'later'])->name("feed.watch-later");
    Route::get('/feed/liked_videos', [PlaylistController::class, 'liked'])->name("feed.liked-videos");
    Route::get('/feed/history', [PlaylistController::class, 'history'])->name("feed.history");
    Route::get('/feed/subscriptions', [SubscriptionsController::class, 'index'])->name("feed.subscriptions");
});
Route::get('/playlist/{playlist:slug}', [PlaylistController::class, 'show'])->name("playlist");

Route::get('/podcasts', function () {
    return Inertia::render('Viewer/Podcasts/PodcastsIndex');
})->name('podcasts');








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




require __DIR__.'/auth.php';
require __DIR__.'/modal.php';
require __DIR__.'/studio.php';
