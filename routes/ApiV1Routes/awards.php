<?php

use App\Http\Controllers\AwardController;
use Illuminate\Support\Facades\Route;

Route::prefix('/award')->name('award.')->group(function () {

    Route::get('/awards', [AwardController::class, 'index'])->name('index');
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/award', [AwardController::class, 'award'])->name('award');
    });
});
