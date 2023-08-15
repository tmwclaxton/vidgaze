<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\CronApiController;
Route::middleware(['cron'])->group(function () {
    Route::prefix('/cron')->name('cron.')->group(function () {

        // telescope prune endpoint
        Route::post('/telescope/prune', [CronApiController::class, 'telescope'])->name('telescope.prune');

        // prune sanctum tokens
        Route::post('/sanctum/prune', [CronApiController::class, 'sanctumTokens'])->name('sanctum.tokens.prune');

        // refresh one twitch category
        Route::post('/refresh/one_twitch_category', [CronApiController::class, 'refreshOneTwitchCategory'])->name('refresh.one_twitch_category');

        // refresh twitch category info
        Route::post('/refresh/twitch_category_info', [CronApiController::class, 'refreshTwitchCategoryInfo'])->name('refresh.twitch_category_info');

        // refresh streams
        Route::post('/refresh/streams', [CronApiController::class, 'refreshStreams'])->name('refresh.streams');

        // delete old live viewers
        Route::post('/live_viewers/prune', [CronApiController::class, 'liveViewersPrune'])->name('live_viewers.prune');

        // refresh subscriptions
        Route::post('/refresh/subscriptions', [CronApiController::class, 'refreshSubscriptions'])->name('refresh.subscriptions');

        // store logs to s3
        Route::post('/logs/backup', [CronApiController::class, 'backupLogs'])->name('logs.backup');

    });
});
