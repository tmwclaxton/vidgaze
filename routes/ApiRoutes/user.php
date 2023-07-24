<?php

use App\Http\Controllers\ApiControllers\UserApiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/profile', [UserApiController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [UserApiController::class, 'destroy'])->name('profile.destroy');
});
