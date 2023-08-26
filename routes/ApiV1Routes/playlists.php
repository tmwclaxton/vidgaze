<?php

use App\Http\Controllers\ApiControllers\PlaylistApiController;
use App\Http\Controllers\ApiControllers\PlaylistVideoApiController;

Route::middleware(['throttle:60,1','auth:sanctum'])->prefix('playlist')->name('playlist.')->group(function () {


    //create playlist
    Route::get('/index', [PlaylistApiController::class, 'index'])->name('index');
    Route::post('/create', [PlaylistApiController::class, 'create'])->name('create');
    Route::patch('/update', [PlaylistApiController::class, 'update'])->name('update');
    Route::delete('/destroy', [PlaylistApiController::class, 'delete'])->name('destroy');

    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/videos/', [PlaylistVideoApiController::class, 'destroy'])
        ->name('video.destroy');

    Route::post('/videos/', [PlaylistVideoApiController::class, 'create'])
        ->name('video.create');


});
Route::middleware(['throttle:60,1','auth.sanctum.switch'])->prefix('playlist')->name('playlist.')->group(function () {
    // get playlist, available to all and authenticated users
    Route::get('show/{slug}', [PlaylistApiController::class, 'show'])->name('show');
    Route::get('videos/index', [PlaylistVideoApiController::class, 'index'])->name('videos.index');

});


