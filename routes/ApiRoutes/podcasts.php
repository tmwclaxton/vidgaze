<?php

use App\Http\Controllers\Content\PodcastController;
use Illuminate\Support\Facades\Route;

Route::get('/podcasts/index', [PodcastController::class, 'index'])->name('podcasts.index');

// modal routes //throttle to 30 requests per minute
Route::middleware(['throttle:30,1','auth'])->group(function () {

    // This allows users to add and remove a like or dislike from a podcast.
    Route::post('/podcasts/{podcastId}/like', [PodcastInteractionController::class, 'toggleLike'])
        ->name('podcast.like.toggle');

    // this get the details of a podcast for the content modal or viewing the podcast or short
    Route::get('/podcasts/{podcastId}/interaction', [PodcastInteractionController::class,"getInteraction"])->name('podcast.interaction');

});
