<?php
//channel routes & creator routes
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\CreatorInteractionController;

Route::get('channel/{creator:slug}', [CreatorController::class,'show'])->name("channel.show");

// modal routes //throttle to 20 requests per minute
Route::middleware(['throttle:60,1','auth'])->group(function () {

    // this allows users to toggle a channel disinterest on a creatorinteraction record
    Route::post('/channels/{channelId}/disinterest', [CreatorInteractionController::class, 'toggleDisinterest'])
        ->name('channel.disinterest.toggle');

    //this lets users subscribe and unsubscribe from a channel
    Route::post('/channels/{channelId}/subscribe', [CreatorInteractionController::class, 'toggleSubscription'])
        ->name('channel.subscription.toggle');

    Route::post('/channels/{channelId}/report', [CreatorInteractionController::class, 'toggleReport'])
        ->name('channel.subscription.report');

});

//creator routes
Route::get('creator/infinite', [CreatorController::class,'infinite'])->name("creator.infinite");
