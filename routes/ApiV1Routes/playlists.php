<?php

use App\Http\Controllers\ApiControllers\PlaylistApiController;
use App\Http\Controllers\ApiControllers\PlaylistVideoApiController;

Route::middleware(['throttle:60,1','auth:sanctum'])->group(function () {

    //get user playlists
    Route::post('/playlist_modal_refresh', [PlaylistApiController::class, 'playlist_modal_refresh'])->name('playlists.modal.refresh');

    //create playlist
    Route::post('/playlist/create', [PlaylistApiController::class, 'create']);
    Route::patch('/playlist/update', [PlaylistApiController::class, 'update']);
    Route::delete('/playlist/destroy', [PlaylistApiController::class, 'delete']);

    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/playlist/videos/', [PlaylistVideoApiController::class, 'destroy'])
        ->name('playlist.video.destroy');

    Route::post('/playlist/videos/', [PlaylistVideoApiController::class, 'create'])
        ->name('playlist.video.create');


});

// get playlist, available to all and authenticated users
Route::get('/playlist/{slug}', [PlaylistApiController::class, 'show'])->middleware('auth.sanctum.switch')->name('playlist.show');

