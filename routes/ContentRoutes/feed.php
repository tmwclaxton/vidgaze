<?php

//user feed routes
use App\Http\Controllers\Content\CreatorInteractionController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PlaylistVideoController;

Route::middleware('auth')->group(function () {
    Route::get('/feed/library', [PlaylistController::class, 'index'])->name("feed.library");
    Route::post('/feed/playlist/{playlist:slug}', [PlaylistController::class, 'update'])->name("playlist.update");
    Route::get('/feed/watch_later', [PlaylistController::class, 'later'])->name("feed.watch-later");
    Route::get('/feed/liked_videos', [PlaylistController::class, 'liked'])->name("feed.liked-videos");
    Route::get('/feed/history', [PlaylistController::class, 'history'])->name("feed.history");
    Route::get('/feed/subscriptions', [CreatorInteractionController::class, 'index'])->name("feed.subscriptions");

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



});

