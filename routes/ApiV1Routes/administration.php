<?php
// Administration Routes

use App\Http\Controllers\AdministratorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('change_user_role', [AdministratorController::class, 'changeUserRole'])->name('change_user_role');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('list_moderators', [AdministratorController::class, 'listModerators'])->name('list_moderators');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('list_mod_actions', [AdministratorController::class, 'listModActions'])->name('list_mod_actions');
    });


});

Route::middleware(['auth:sanctum', 'moderator'])->group(function () {
    Route::prefix('moderator')->name('moderator.')->group(function () {
    });
});



