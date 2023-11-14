<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\CronApiController;
Route::middleware(['vidgaze.api.key'])->group(function () {
    Route::prefix('/cron')->name('cron.')->group(function () {

        // telescope prune endpoint // tested
        Route::post('/telescope/prune', [CronApiController::class, 'telescope'])->name('telescope.prune');

        // prune sanctum tokens // tested
        Route::post('/sanctum/prune', [CronApiController::class, 'sanctumTokens'])->name('sanctum.tokens.prune');

        // refresh one twitch category
        Route::post('/refresh/top_categories', [CronApiController::class, 'refreshTopCategories'])->name('refresh.top_categories');

        // refresh twitch category info
        Route::post('/refresh/twitch_category_info', [CronApiController::class, 'refreshTwitchCategoryInfo'])->name('refresh.twitch_category_info');

        // refresh streams
        Route::post('/refresh/streams', [CronApiController::class, 'refreshStreams'])->name('refresh.streams');

        // prune old live viewers // tested
        Route::post('/live_viewers/prune', [CronApiController::class, 'liveViewersPrune'])->name('live_viewers.prune');

        // refresh subscriptions
        Route::post('/refresh/subscriptions', [CronApiController::class, 'refreshSubscriptions'])->name('refresh.subscriptions');

        // store logs to s3 // tested
        Route::post('/logs/backup', [CronApiController::class, 'backupLogs'])->name('logs.backup');

        // prune batch logs // tested
        Route::post('/batches/prune', [CronApiController::class, 'pruneBatches'])->name('batches.prune');

    });
});
