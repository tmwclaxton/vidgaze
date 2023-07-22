<?php


use App\Http\Controllers\ApiControllers\VideoApiController;
use App\Http\Controllers\ApiControllers\VideoInteractionApiController;
use Illuminate\Support\Facades\Route;


Route::get('/videos/infinite', [VideoApiController::class, 'index'])->name('videos.infinite');

Route::middleware(['auth'])->group(function () {

    // This allows users to create and destroy VideoDisinterest records, indicating that they are not interested in a particular video.
    Route::post('/videos/{videoId}/disinterest', [VideoInteractionApiController::class, 'toggleDisinterest'])
        ->name('video.disinterest.toggle');

    // This allows users to add and remove a like or dislike from a video.
    Route::post('/videos/{videoId}/like', [VideoInteractionApiController::class, 'toggleLike'])
        ->name('video.like.toggle');

    Route::post('/videos/{videoId}/dislike', [VideoInteractionApiController::class, 'toggleDislike'])
        ->name('video.dislike.toggle');

    Route::post('/videos/{videoId}/report', [VideoInteractionApiController::class, 'toggleReport'])
        ->name('video.report.toggle');

    // this get the details of a video for the content modal or viewing the video or short
    Route::get('/video/{videoId}/details', [VideoInteractionApiController::class,"modalDetails"])->name('video.details');
    // used for like and dislike button
    Route::get('/video/{videoId}/interaction', [VideoInteractionApiController::class,"getVideoInteraction"])->name('video.interaction');


});
