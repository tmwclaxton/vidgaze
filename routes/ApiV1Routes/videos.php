<?php


use App\Http\Controllers\ApiControllers\VideoApiController;
use App\Http\Controllers\ApiControllers\VideoInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('/video')->name('video')->group(function () {

    Route::get('/index', [VideoApiController::class, 'index'])->name('index');
    Route::get('{slug}', [VideoApiController::class, 'show'])->middleware('auth.sanctum.switch')->name('show');

    Route::middleware(['auth:sanctum'])->group(function () {

        // This allows users to create and destroy VideoDisinterest records, indicating that they are not interested in a particular video
        Route::post('/{video_id}/disinterest', [VideoInteractionApiController::class, 'toggleDisinterest'])
            ->name('disinterest.toggle');

        // This allows users to add and remove a like or dislike from a video
        Route::post('/{video_id}/like', [VideoInteractionApiController::class, 'toggleLike'])
            ->name('like.toggle');

        Route::post('/{video_id}/dislike', [VideoInteractionApiController::class, 'toggleDislike'])
            ->name('dislike.toggle');

        Route::post('/{video_id}/report', [VideoInteractionApiController::class, 'toggleReport'])
            ->name('report.toggle');

        // this get the details of a video for the content modal or viewing the video or short
        Route::get('/{video_id}/details', [VideoInteractionApiController::class, "modalDetails"])->name('details');
        // used for like and dislike button
        Route::get('/{video_id}/interaction', [VideoInteractionApiController::class, "getVideoInteraction"])->name('interaction');


    });

});
