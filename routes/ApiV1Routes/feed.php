<?php

// modal routes //throttle to 20 requests per minute

use App\Http\Controllers\ApiControllers\CreatorInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1','auth:sanctum'])->group(function () {


    // grab subscription data
    Route::get('/feed/subscriptions', [CreatorInteractionApiController::class, 'getSubscriptionFeed'])->name("feed.subscriptions.data");

    // grab channels subscribed to
    Route::get('/feed/channels', [CreatorInteractionApiController::class, 'getSubscriptions'])->name("feed.channels.data");


});
