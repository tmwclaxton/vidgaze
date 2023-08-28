<?php


use App\Http\Controllers\ApiControllers\StreamApiController;
use App\Http\Controllers\ApiControllers\StreamInteractionApiController;


Route::prefix('/stream')->name('stream.')->group(function () {

    Route::get('/index', [StreamApiController::class, 'index'])->name("index")->middleware('throttle:60,1');
    Route::get('{slug}', [StreamApiController::class, 'show'])->middleware('auth.sanctum.switch')->name('show');


    Route::middleware(['throttle:30,1', 'auth:sanctum'])->group(function () {

        Route::post('/{stream_id}/disinterest', [StreamInteractionApiController::class, 'toggleDisinterest'])
            ->name('disinterest.toggle');

        Route::post('/{stream_id}/report', [StreamInteractionApiController::class, 'toggleReport'])
            ->name('report.toggle');

        Route::get('/{stream_id}/details', [StreamInteractionApiController::class, "modalDetails"])->name('details');

        // if we need to get the interaction details for a stream down the road
        Route::get('/{stream_id}/interaction', [StreamInteractionApiController::class,"getStreamInteraction"])->name('interaction');

    });
});
