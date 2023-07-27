<?php

use App\Http\Controllers\ApiControllers\PodcastApiController;
use App\Http\Controllers\ApiControllers\PodcastInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('podcast')->name('podcast')->group(function () {


    Route::get('/index', [PodcastApiController::class, 'index'])->name('podcasts.index');

    // modal routes //throttle to 30 requests per minute
    Route::middleware(['throttle:30,1', 'auth:sanctum'])->group(function () {

        // This allows users to add and remove a like or dislike from a podcast.
        Route::post('{podcastId}/love', [PodcastInteractionApiController::class, 'toggleLove'])
            ->name('love.toggle');

        // this get the details of a podcast for the content modal or viewing the podcast or short
        Route::get('/{podcastId}/interaction', [PodcastInteractionApiController::class, "getInteraction"])->name('interaction');

    });

});
