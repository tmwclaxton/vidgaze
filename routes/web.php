<?php

use App\Http\Controllers\Content\ChannelDisinterestController;
use App\Http\Controllers\Content\PlaylistVideoController;
use App\Http\Controllers\Content\VideoDisinterestController;
use App\Http\Controllers\Content\VideoReportController;
use App\Http\Controllers\Infinite\InfiniteVideosController;
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
    $videos = Video::inRandomOrder()->take(6)->get()->map(function ($video) {
        return $video->frontEndDetails();
    });

    return Inertia::render('Viewer/Home/Homepage', [
        'videos' => $videos,
    ]);
})->name('home');


//video routes
Route::get('/videos',[VideoController::class,'index'])->name('videos.index');
Route::get('/shorts', [VideoController::class,'shorts'])->name('videos.shorts');
Route::get('watch/{video:slug}', [VideoController::class,'show'])->name("watch");
Route::get('watch/{video:slug}/{playlist:slug}', [VideoController::class,'playlist'])->name("watch.playlist");
Route::get('watch/{video:slug}/{playlist:slug}/shuffle', [VideoController::class,'shuffle'])->name("watch.playlist.shuffle");

//stream routes
Route::get('livestreams', [StreamController::class,'index'])->name("streams.index");
Route::get('stream/{stream:slug}', [StreamController::class,'show'])->name("stream");

//podcast routes
Route::get('podcasts', [PodCastController::class,'index'])->name("podcast.index");
Route::get('podcast/', [PodCastController::class,'show'])->name("podcast.show");
Route::get('podcast/episode/', [PodCastController::class,'episode'])->name("podcast.episode");

//music routes
Route::get('/music', [MusicController::class,'index'])->name('music.index');
Route::get('music/album', [MusicController::class,'album'])->name('music.album');
Route::get('music/category', [MusicController::class,'category'])->name('music.category');
Route::get('music/track', [MusicController::class,'track'])->name('music.track');

//feed routes
Route::middleware('auth')->group(function () {
    Route::get('feed/library', [PlaylistController::class, 'index'])->name("feed.library");
    Route::post('feed/playlist/{playlist:slug}', [PlaylistController::class, 'update'])->name("playlist.update");
    Route::get('feed/watch-later', [PlaylistController::class, 'later'])->name("feed.watch-later");
    Route::get('feed/liked-videos', [PlaylistController::class, 'liked'])->name("feed.liked-videos");
    Route::get('feed/history', [PlaylistController::class, 'history'])->name("feed.history");
    Route::get('feed/subscriptions', [SubscriptionsController::class, 'index'])->name("feed.subscriptions");
});
Route::get('feed/playlist/{playlist:slug}', [PlaylistController::class, 'show'])->name("playlist");






Route::get('/podcasts', function () {
    return Inertia::render('Viewer/Podcasts/PodcastsIndex');
})->name('podcasts');


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// studio routes
Route::middleware('auth')->group(function () {
    Route::get('/studio', function () {
        return Inertia::render('Studio/Dashboard', [
            'sources' => auth()->user()->creator->sources,
            'showingStudioLinks' => true,
        ]);
    })->name("studio.dashboard") ;
    Route::get('studio/upload',  [VideoUploadController::class, 'edit'])->name("studio.upload");
    Route::post('studio/upload', [VideoUploadController::class, 'upload']);
    Route::get('studio/video/{video:slug}', [VideoController::class,'edit'])->name("studio.video.edit");
    Route::get('studio/stream/{stream:slug}', [StreamController::class,'edit'])->name("studio.stream.edit");
    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");
    Route::get('import', [ImportingController::class,'index'])->name('importing.index');
    Route::get('import/{platform}', [ImportingController::class,'import']);
    Route::get('import/login/{platform}', [ImportingController::class,'logIn']);
    Route::get('studio/link/{platform}', [LinkingController::class,'link']);
    Route::post('studio/login/{platform}', [LinkingController::class,'logIn']);
});
Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

//oauth routes

//search routes
    Route::get('/search/', [SearchController::class, 'get']);


Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchBarController::class, 'get'])->name('search_suggestions');
});


// adding or removing a video from a playlist //throttle to 20 requests per minute
Route::middleware(['throttle:60,1','auth'])->group(function () {
    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/playlists/{playlistId}/videos/{videoId}', [PlaylistVideoController::class, 'destroy'])
        ->name('playlist.video.destroy');

    Route::post('/playlists/{playlistId}/videos/{videoId}', [PlaylistVideoController::class, 'create'])
        ->name('playlist.video.create');

    // this allows users to create and destroy ChannelDisinterest records, indicating that they are not interested in a particular creator's channel.
    Route::post('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'create'])
        ->name('channel.disinterest.create');

    Route::delete('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'destroy'])
        ->name('channel.disinterest.destroy');


    // This allows users to create and destroy VideoDisinterest records, indicating that they are not interested in a particular video.
    Route::post('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'create'])
        ->name('video.disinterest.create');

    Route::delete('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'destroy'])
        ->name('video.disinterest.destroy');


    // this get the details of a video for the content modal
    Route::get('/videos/{videoId}/details', [VideoController::class,"details"])->name('videos.details');


});

// limit to 2 requests per hour
Route::post('/videos/{videoId}/report', [VideoController::class, 'report'])
    ->name('video.report.add')
    ->middleware('throttle:2,60');









//about
Route::get('/about', function () { return Inertia::render('Viewer/Landing');})->name('about');

//policy and terms
Route::get('/terms_of_service', function () { return Inertia::render('Legal/Terms'); })->name('terms');
Route::get('/privacy_policy', function () { return Inertia::render('Legal/Policy'); })->name('privacy');

//admin routes
Route::middleware('admin')->group(function () {
    Route::get('/admin', function () { return Inertia::render('Admin/AdminDashboard'); })->name('admin.dashboard');
    Route::get('/component-testing', function () { return Inertia::render('Admin/TestComponents'); })->name('component-testing');
});

//testing routes
if (App::environment('local')) {
    Route::post('/test', function() { return redirect()->back()->with('toast', 'Toast endpoint!'); });
}


require __DIR__.'/auth.php';
