<?php


use App\Http\Controllers\Content\ChannelDisinterestController;
use App\Http\Controllers\Content\PlaylistController;
use App\Http\Controllers\Content\PlaylistVideoController;
use App\Http\Controllers\Content\ShareController;
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\SubscriptionsController;
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Content\VideoDisinterestController;

// modal routes //throttle to 20 requests per minute
Route::middleware(['throttle:60,1','auth'])->group(function () {

    //get user playlists
    Route::get('/playlist_modal_refresh', [PlaylistController::class, 'playlist_modal_refresh'])->name('playlists.modal.refresh');

    //create playlist
    Route::post('/playlist/create', [PlaylistController::class, 'create']);

    // This allows users to create and destroy PlaylistVideo records, indicating that they have added/removed a particular video to a particular playlist.
    Route::delete('/playlists/{playlistId}/videos/', [PlaylistVideoController::class, 'destroy'])
        ->name('playlist.video.destroy');

    Route::post('/playlists/{playlistId}/videos/', [PlaylistVideoController::class, 'create'])
        ->name('playlist.video.create');



    // this allows users to create and destroy ChannelDisinterest records, indicating that they are not interested in a particular creator's channel.
    Route::post('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'create'])
        ->name('channel.disinterest.create');

    Route::delete('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'destroy'])
        ->name('channel.disinterest.destroy');

    Route::post('/channels/{channelId}/subscribe', [SubscriptionsController::class, 'create'])
        ->name('channel.subscription.create');

    Route::delete('/channels/{channelId}/unsubscribe', [SubscriptionsController::class, 'destroy'])
        ->name('channel.subscription.destroy');




    // This allows users to create and destroy VideoDisinterest records, indicating that they are not interested in a particular video.
    Route::post('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'create'])
        ->name('video.disinterest.create');

    Route::delete('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'destroy'])
        ->name('video.disinterest.destroy');

    Route::post('/videos/{videoId}/like', [VideoController::class, 'like'])
        ->name('video.like.toggle');

    Route::post('/videos/{videoId}/dislike', [VideoController::class, 'dislike'])
        ->name('video.dislike.toggle');


    // this get the details of a video for the content modal
    Route::get('/videos/{videoId}/details', [VideoController::class,"details"])->name('videos.details');


});


Route::get('/shares', [ShareController::class, 'index'])->name('share.index');
// limit to 5 requests per 15 minutes

Route::post('/videos/{id}/report', [VideoController::class, 'report'])
    ->name('video.report.add')
    ->middleware('throttle:555,15');

Route::post('/streams/{id}/report', [StreamController::class, 'report'])
    ->name('stream.report.add')
    ->middleware('throttle:555,15');



