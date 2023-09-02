<?php

// modal routes //throttle to 20 requests per minute
use App\Http\Controllers\ApiControllers\CreatorApiController;
use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('creator')->name('creator.')->group(function () {
    //creator routes
    Route::get('index', [CreatorApiController::class, 'index'])->name("index");
    Route::get('{slug}', [CreatorApiController::class, 'show'])->name("show");

    // interaction routes
    Route::middleware(['throttle:60,1', 'auth:sanctum'])->group(function () {
        // toggle featured creator
        Route::post('/feature', [CreatorApiController::class, 'toggleFeatured'])
            ->name('feature.toggle');

        //update creator
        Route::patch('/update', [CreatorApiController::class, 'update'])
            ->name('update');

        //update creator profile picture
        Route::patch('/update/avatar', [CreatorApiController::class, 'updateProfilePicture'])
            ->name('update.avatar');

        //update creator banner picture
        Route::patch('/update/banner', [CreatorApiController::class, 'updateProfileBanner'])
            ->name('update.banner');

        // this allows users to toggle a channel disinterest on a creatorinteraction record
        Route::post('/{channelId}/disinterest', [CreatorInteractionApiController::class, 'toggleDisinterest'])
            ->name('disinterest.toggle');

        //this lets users subscribe and unsubscribe from a channel
        Route::post('/{channelId}/subscribe', [CreatorInteractionApiController::class, 'toggleSubscription'])
            ->name('subscription.toggle');

        Route::post('/{channelId}/report', [CreatorInteractionApiController::class, 'toggleReport'])
            ->name('subscription.report');
    });
});
