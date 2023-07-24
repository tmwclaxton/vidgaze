<?php

// modal routes //throttle to 20 requests per minute

use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;
use App\Http\Controllers\ApiControllers\PlaylistApiController;
use App\Http\Controllers\ApiControllers\PlaylistVideoApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1','auth:sanctum'])->group(function () {

    //get user playlists
    Route::get('/playlist_modal_refresh', [PlaylistApiController::class, 'playlist_modal_refresh'])->name('playlists.modal.refresh');

    //create playlist
    Route::post('/playlist/create', [PlaylistApiController::class, 'create']);
    Route::patch('/playlist/update', [PlaylistApiController::class, 'update']);
    Route::delete('/playlist/destroy', [PlaylistApiController::class, 'delete']);

    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/playlist/videos/', [PlaylistVideoApiController::class, 'destroy'])
        ->name('playlist.video.destroy');

    Route::post('/playlist/videos/', [PlaylistVideoApiController::class, 'create'])
        ->name('playlist.video.create');

    // grab subscription data
    Route::get('/feed/subscriptions', [CreatorInteractionApiController::class, 'getSubscriptionFeed'])->name("feed.subscriptions.data");

    // grab channels subscribed to
    Route::get('/feed/channels', [CreatorInteractionApiController::class, 'getSubscriptions'])->name("feed.channels.data");


});

// get playlist, available to all and authenticated users
Route::get('/playlist/{slug}', [PlaylistApiController::class, 'show'])->middleware('auth.sanctum.switch')->name('playlist.show');

