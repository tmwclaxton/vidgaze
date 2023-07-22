<?php

// modal routes //throttle to 20 requests per minute
use App\Http\Controllers\ApiControllers\CreatorApiController;
use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1','auth'])->group(function () {

    // this allows users to toggle a channel disinterest on a creatorinteraction record
    Route::post('/channels/{channelId}/disinterest', [CreatorInteractionApiController::class, 'toggleDisinterest'])
        ->name('channel.disinterest.toggle');

    //this lets users subscribe and unsubscribe from a channel
    Route::post('/channels/{channelId}/subscribe', [CreatorInteractionApiController::class, 'toggleSubscription'])
        ->name('channel.subscription.toggle');

    Route::post('/channels/{channelId}/report', [CreatorInteractionApiController::class, 'toggleReport'])
        ->name('channel.subscription.report');

});

//creator routes
Route::get('creator/index', [CreatorApiController::class,'index'])->name("creator.index");

// toggle featured creator
Route::middleware(['auth','admin'])->group(function () {
    Route::post('/creator/featured', [CreatorApiController::class, 'toggleFeatured'])
        ->name('creator.featured.toggle');
});
