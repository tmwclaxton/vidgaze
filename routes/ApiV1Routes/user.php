<?php

use App\Http\Controllers\ApiControllers\UserApiController;

Route::middleware('auth:sanctum')->prefix('profile')->name('profile.')->group(function () {
    Route::patch('/update', [UserApiController::class, 'update'])->name('update');
    Route::delete('/destroy', [UserApiController::class, 'destroy'])->name('destroy');
});
