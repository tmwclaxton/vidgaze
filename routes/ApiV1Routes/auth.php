<?php
// Auth
use App\Http\Controllers\ApiControllers\AuthApiController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthApiController::class, 'signup'])->name('auth.register');
    Route::post('login', [AuthApiController::class, 'login'])->name('auth.login');
    Route::post('logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum')->name('auth.logout');
    Route::get('user', [AuthApiController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum')->name('auth.user');

    Route::post('/password/email', [AuthApiController::class, 'sendPasswordResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');


    Route::post('/password/reset', [AuthApiController::class, 'resetPassword'])->name('password.reset');
    Route::post('/password/confirm', [AuthApiController::class, 'confirmPassword'])->middleware('auth:sanctum')->name('password.confirm');
    Route::patch('/password/update', [AuthApiController::class, 'updatePassword'])->middleware('auth:sanctum')->name('password.change');


    Route::post('/email/verify/', [AuthApiController::class, 'sendEmailVerificationLink'])->middleware(['auth:sanctum', 'throttle:12,1'])->name('verification.verify');
    Route::get('/email/verify/', [AuthApiController::class, 'verifyEmail'])->name('verification.verify');

    // check token privileges
    Route::get('/token/privileges', [AuthApiController::class, 'checkTokenPrivileges'])->middleware('auth:sanctum')->name('token.privileges');
    // get updated token
    Route::get('/token/refresh', [AuthApiController::class, 'refreshToken'])->middleware('auth:sanctum')->name('token.refresh');

});
