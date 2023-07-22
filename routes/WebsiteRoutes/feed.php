<?php

//user feed routes
use App\Http\Controllers\WebControllers\FeedWebController;
use App\Http\Controllers\WebControllers\PlaylistWebController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/feed/library', [PlaylistWebController::class, 'index'])->name("feed.library");
    Route::post('/feed/playlist/{playlist:slug}', [PlaylistWebController::class, 'update'])->name("playlist.update");
    Route::get('/feed/watch_later', [PlaylistWebController::class, 'later'])->name("feed.watch-later");
    Route::get('/feed/liked_videos', [PlaylistWebController::class, 'liked'])->name("feed.liked-videos");
    Route::get('/feed/history', [PlaylistWebController::class, 'history'])->name("feed.history");

    Route::get('/feed/subscriptions', [FeedWebController::class, 'subscriptions_index'])->name("feed.subscriptions");
    Route::get('feed/channels', [FeedWebController::class, 'channels_index'])->name("feed.channels");

});
Route::get('/playlist/{playlist:slug}', [PlaylistWebController::class, 'show'])->name("playlist");

