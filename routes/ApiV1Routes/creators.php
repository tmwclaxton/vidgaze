<?php

// modal routes //throttle to 20 requests per minute
use App\Http\Controllers\ApiControllers\CreatorApiController;
use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('creator')->name('creator.')->group(function () {
    //creator routes
    Route::get('index', [CreatorApiController::class, 'index'])->name("index");
    Route::get('{slug}', [CreatorApiController::class, 'show'])->name("show");

    Route::get('{slug}/videos', [CreatorApiController::class, 'videos'])->name("videos");

    // toggle featured creator
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/feature', [CreatorApiController::class, 'toggleFeatured'])
            ->name('feature.toggle');
    });

    // interaction routes
    Route::middleware(['throttle:60,1', 'auth:sanctum'])->group(function () {
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
