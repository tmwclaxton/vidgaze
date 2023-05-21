<?php


//podcast routes
use App\Http\Controllers\Content\PodcastController;

Route::get('/podcasts', [PodCastController::class,'index'])->name("podcasts.index");
Route::get('/podcast/', [PodCastController::class,'show'])->name("podcast.show");
Route::get('/podcast/episode/', [PodCastController::class,'episode'])->name("podcast.episode");
Route::get('/podcasts/infinite', [PodcastController::class, 'infinite'])->name('podcasts.infinite');

// modal routes //throttle to 30 requests per minute
Route::middleware(['throttle:30,1','auth'])->group(function () {

    // This allows users to add and remove a like or dislike from a podcast.
    Route::post('/podcasts/{podcastId}/like', [PodcastController::class, 'like'])
        ->name('podcast.like.toggle');

    // this get the details of a podcast for the content modal or viewing the podcast or short
    Route::get('/podcasts/{podcastId}/view_info', [PodcastController::class,"getPodcastViewInfo"])->name('podcasts.view.info');

});
