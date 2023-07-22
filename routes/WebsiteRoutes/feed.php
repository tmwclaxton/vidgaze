<?php

//user feed routes
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\CreatorInteractionController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PlaylistVideoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/feed/library', [PlaylistController::class, 'index'])->name("feed.library");
    Route::post('/feed/playlist/{playlist:slug}', [PlaylistController::class, 'update'])->name("playlist.update");
    Route::get('/feed/watch_later', [PlaylistController::class, 'later'])->name("feed.watch-later");
    Route::get('/feed/liked_videos', [PlaylistController::class, 'liked'])->name("feed.liked-videos");
    Route::get('/feed/history', [PlaylistController::class, 'history'])->name("feed.history");

    Route::get('/feed/subscriptions', [CreatorInteractionController::class, 'subscriptions_index'])->name("feed.subscriptions");
    Route::get('feed/channels', [CreatorInteractionController::class, 'channels_index'])->name("feed.channels");

});
Route::get('/playlist/{playlist:slug}', [PlaylistController::class, 'show'])->name("playlist");

