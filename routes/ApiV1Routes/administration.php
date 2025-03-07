<?php
// Administration Routes

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\ApiControllers\CategoryApiController;
use App\Http\Controllers\PinController;
use Illuminate\Support\Facades\Route;


// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::post('change_user_role', [AdministratorController::class, 'changeUserRole'])->name('change_user_role');
        Route::get('list_moderators', [AdministratorController::class, 'listModerators'])->name('list_moderators');
        Route::get('list_mod_actions', [AdministratorController::class, 'listModActions'])->name('list_mod_actions');
    });
});

// Moderator Routes
Route::middleware(['auth:sanctum', 'moderator'])->group(function () {
    Route::prefix('moderator')->name('moderator.')->group(function () {
        Route::post('get_pin_status', [PinController::class, 'getPinStatus'])->name('get_pin_status');
        Route::post('pin_video', [PinController::class, 'pinVideo'])->name('pin_video');
        Route::post('unpin_video', [PinController::class, 'unpinVideo'])->name('unpin_video');
        Route::post('add_category', [CategoryApiController::class, 'addCategoryToVideo'])->name('add_category');
        Route::post('remove_category', [CategoryApiController::class, 'removeCategoryFromVideo'])->name('remove_category');
    });
});



