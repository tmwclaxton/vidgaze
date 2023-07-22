<?php


use App\Http\Controllers\ApiControllers\StreamInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:30,1','auth'])->group(function () {
    Route::post('/stream/{streamId}/report', [StreamInteractionApiController::class, 'toggleReport'])
        ->name('stream.report.toggle');

    Route::post('/stream/{streamId}/disinterest', [StreamInteractionApiController::class, 'toggleDisinterest'])
        ->name('stream.disinterest.toggle');

    Route::get('/stream/{streamId}/details', [StreamInteractionApiController::class,"modalDetails"])->name('stream.details');

    // if we need to get the interaction details for a stream down the road
    //Route::get('/stream/{streamId}/interaction', [StreamInteractionController::class,"getStreamInteraction"])->name('stream.view.interaction');

});
