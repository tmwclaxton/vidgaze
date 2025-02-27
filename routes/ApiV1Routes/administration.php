<?php
// Administration Routes

use App\Http\Controllers\AdministratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('change_user_role', [AdministratorController::class, 'changeUserRole'])->name('change_user_role');
    });


});

Route::middleware(['auth:sanctum', 'moderator'])->group(function () {
    Route::prefix('moderator')->name('moderator.')->group(function () {
    });
});



