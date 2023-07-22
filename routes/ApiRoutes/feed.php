<?php

// modal routes //throttle to 20 requests per minute
use App\Http\Controllers\Content\CreatorInteractionController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PlaylistVideoController;

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

