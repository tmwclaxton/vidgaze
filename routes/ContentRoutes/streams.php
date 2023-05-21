<?php


//stream routes
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\StreamInteractionController;

Route::get('/livestreams', [StreamController::class,'index'])->name("streams.index");
Route::get('/streams/top', [StreamController::class,'topStreams'])->name("streams.top");
Route::get('/stream/{stream:slug}', [StreamController::class,'show'])->name("stream.show");

Route::middleware(['throttle:30,1','auth'])->group(function () {
    Route::post('/stream/{streamId}/report', [StreamInteractionController::class, 'toggleReport'])
        ->name('stream.report.toggle');

    Route::post('/stream/{streamId}/disinterest', [StreamInteractionController::class, 'toggleDisinterest'])
        ->name('stream.disinterest.toggle');

    Route::get('/stream/{streamId}/details', [StreamInteractionController::class,"modalDetails"])->name('stream.details');

    // if we need to get the interaction details for a stream down the road
    //Route::get('/stream/{streamId}/interaction', [StreamInteractionController::class,"getStreamInteraction"])->name('stream.view.interaction');

});


