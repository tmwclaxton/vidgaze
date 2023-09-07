<?php

//user feed routes
use App\Http\Controllers\WebControllers\FeedWebController;
use App\Http\Controllers\WebControllers\PlaylistWebController;
use Illuminate\Support\Facades\Route;
Route::prefix('feed')->name('feed.')->middleware(['auth.flag.cookie'])->group(function () {

    Route::get('/library', [FeedWebController::class, 'library'])->name("library");
    Route::get('/subscriptions', [FeedWebController::class, 'subscriptions'])->name("subscriptions");
    Route::get('feed/channels', [FeedWebController::class, 'channels'])->name("channels");

    Route::get('/watch_later', function () {
        return redirect()->route('playlist.show', ['slug' => 'watch_later']);
    })->name("watch-later");

    Route::get('/liked_videos', function () {
        return redirect()->route('playlist.show', ['slug' => 'liked_videos']);
    })->name("liked-videos");

    Route::get('/history', function () {
        return redirect()->route('playlist.show', ['slug' => 'history']);
    })->name("history");
})->middleware(['auth.flag.cookie']);

Route::get('/playlist/{slug}', [PlaylistWebController::class, 'show'])->name("playlist.show");

