<?php


use App\Http\Controllers\ApiControllers\StreamApiController;
use App\Http\Controllers\ApiControllers\StreamInteractionApiController;


Route::prefix('/stream')->name('stream.')->group(function () {

    Route::get('/index', [StreamApiController::class, 'index'])->name("index");
    Route::get('{slug}', [StreamApiController::class, 'show'])->middleware('auth.sanctum.switch')->name('show');


    Route::middleware(['throttle:30,1', 'auth:sanctum'])->group(function () {

        Route::post('/{streamId}/disinterest', [StreamInteractionApiController::class, 'toggleDisinterest'])
            ->name('disinterest.toggle');

        Route::post('/{streamId}/report', [StreamInteractionApiController::class, 'toggleReport'])
            ->name('report.toggle');

        Route::get('/{streamId}/details', [StreamInteractionApiController::class, "modalDetails"])->name('details');

        // if we need to get the interaction details for a stream down the road
        Route::get('/{streamId}/interaction', [StreamInteractionApiController::class,"getStreamInteraction"])->name('interaction');

    });
});
