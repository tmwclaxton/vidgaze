<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoUploadController;
use Illuminate\Foundation\Application;
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

Route::middleware('admin')->group(function () {
    Route::get('/admin', function () { return Inertia::render('Admin/AdminDashboard'); })->name('admin.dashboard');
    Route::get('/component-testing', function () { return Inertia::render('Admin/TestComponents'); })->name('component-testing');
});

Route::get('/', function () {
    //check if user is logged in
    if (auth()->user() === null) {
        return Inertia::render('Viewer/Landing', [
            'showingStudioLinks' => false,
        ]);
    } else {
        return Inertia::render('Viewer/Homepage', [

        ]);
    }
})->name('home');


Route::get('/about', function () {
    return Inertia::render('Viewer/Landing', [
        'showingStudioLinks' => false,
    ]);
})->name('about');


Route::get('/videos', function () {
    return Inertia::render('Viewer/Videos/VideosIndex');
})->name('videos');

Route::get('/streams', function () {
    return Inertia::render('Viewer/Streams/StreamsIndex');
})->name('streams');

Route::get('/shorts', function () {
    return Inertia::render('Viewer/Shorts/ShortsIndex');
})->name('shorts');

Route::get('/music', function () {
    return Inertia::render('Viewer/Music/MusicIndex');
})->name('music');

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
    })->name("studio.dashboard")->middleware("auth");


});

//policy and terms
Route::get('/terms_of_service', function () {
    return Inertia::render('Legal/Terms');
})->name('terms');

Route::get('/privacy_policy', function () {
    return Inertia::render('Legal/Policy');
})->name('privacy');


Route::post('/test', function() {
    return redirect()->back()->with('toast', 'Toast endpoint!');
});


require __DIR__.'/auth.php';
