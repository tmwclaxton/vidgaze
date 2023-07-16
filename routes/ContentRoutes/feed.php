<?php

//user feed routes
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\CreatorInteractionController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PlaylistVideoController;

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

// modal routes //throttle to 20 requests per minute
Route::middleware(['throttle:60,1','auth'])->group(function () {

    //get user playlists
    Route::get('/playlist_modal_refresh', [PlaylistController::class, 'playlist_modal_refresh'])->name('playlists.modal.refresh');

    //create playlist
    Route::post('/playlist/create', [PlaylistController::class, 'create']);

    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/playlists/{playlistId}/videos/', [PlaylistVideoController::class, 'destroy'])
        ->name('playlist.video.destroy');

    Route::post('/playlists/{playlistId}/videos/', [PlaylistVideoController::class, 'create'])
        ->name('playlist.video.create');




    // grab subscription data
    Route::get('/feed/subscriptions/data', [CreatorInteractionController::class, 'getSubscriptionFeed'])->name("feed.subscriptions.data");

    // grab channels subscribd to
    Route::get('/feed/channels/data', [CreatorInteractionController::class, 'getSubscriptions'])->name("feed.channels.data");


});

