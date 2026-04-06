<?php

use App\Http\Controllers\ApiControllers\PodcastApiController;
use App\Http\Controllers\ApiControllers\PodcastInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('podcast')->name('podcast.')->group(function () {

    Route::get('/index', [PodcastApiController::class, 'index'])->name('podcasts.index');
    Route::get('/infinite', [PodcastApiController::class, 'index'])->name('infinite');

    Route::get('/{podcastSlug}/episode/{episodeSlug}', [PodcastApiController::class, 'episode'])
        ->name('episode.show')
        ->where(['podcastSlug' => '[a-zA-Z0-9\-]+', 'episodeSlug' => '[a-zA-Z0-9\-]+']);

    Route::middleware(['throttle:30,1', 'auth:sanctum'])->group(function () {

        Route::post('{podcastId}/love', [PodcastInteractionApiController::class, 'toggleLove'])
            ->whereNumber('podcastId')
            ->name('love.toggle');

        Route::get('{podcastId}/interaction', [PodcastInteractionApiController::class, 'getInteraction'])
            ->whereNumber('podcastId')
            ->name('interaction');

    });

    Route::get('/{slug}', [PodcastApiController::class, 'show'])
        ->name('show')
        ->where('slug', '[a-zA-Z0-9\-]+');

});
