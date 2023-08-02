<?php

// modal routes //throttle to 20 requests per minute

use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;

Route::middleware(['throttle:60,1','auth:sanctum'])->prefix('feed')->name('feed.')->group(function () {


    // grab subscription data
    Route::get('/subscriptions', [CreatorInteractionApiController::class, 'getSubscriptionFeed'])->name("subscriptions.data");

    // grab channels subscribed to
    Route::get('/channels', [CreatorInteractionApiController::class, 'getSubscriptions'])->name("channels.data");


});
