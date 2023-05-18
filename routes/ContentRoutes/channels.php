<?php
//channel routes
use App\Http\Controllers\Content\ChannelDisinterestController;
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\SubscriptionsController;

Route::get('channel/{creator:slug}', [CreatorController::class,'show'])->name("channel.show");
// modal routes //throttle to 20 requests per minute
Route::middleware(['throttle:60,1','auth'])->group(function () {

    // this allows users to create and destroy ChannelDisinterest records, indicating that they are not interested in a particular creator's channel.
    Route::post('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'create'])
        ->name('channel.disinterest.create');

    Route::delete('/channels/{channelId}/disinterest', [ChannelDisinterestController::class, 'destroy'])
        ->name('channel.disinterest.destroy');

    //this lets users subscribe and unsubscribe from a channel
    Route::post('/channels/{channelId}/subscribe', [SubscriptionsController::class, 'create'])
        ->name('channel.subscription.create');

    Route::delete('/channels/{channelId}/unsubscribe', [SubscriptionsController::class, 'destroy'])
        ->name('channel.subscription.destroy');
});
