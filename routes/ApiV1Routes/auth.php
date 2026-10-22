<?php
// Auth
use App\Http\Controllers\ApiControllers\AuthApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthApiController::class, 'signup'])->middleware('throttle:auth-register')->name('register');
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:auth-login')->name('login');
    Route::post('logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

    Route::get('user', [AuthApiController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum')->name('user');


    Route::post('/password/email', [AuthApiController::class, 'sendPasswordResetLinkEmail'])->middleware('throttle:auth-password')->name('password.email');
    Route::post('/password/reset', [AuthApiController::class, 'resetPassword'])->middleware('throttle:auth-password')->name('password.reset');
    Route::post('/password/confirm', [AuthApiController::class, 'confirmPassword'])->middleware(['auth:sanctum', 'throttle:auth-sensitive'])->name('password.confirm');
    Route::patch('/password/update', [AuthApiController::class, 'updatePassword'])->middleware(['auth:sanctum', 'throttle:auth-sensitive'])->name('password.change');


    // check token privileges
    Route::get('/token/privileges', [AuthApiController::class, 'checkTokenPrivileges'])->middleware('auth:sanctum')->name('token.privileges');
    // get updated token
    Route::get('/token/refresh', [AuthApiController::class, 'refreshToken'])->middleware('auth:sanctum')->name('token.refresh');

    // override email verification route names like remove the prefixes
    Route::post('/email/verify/', [AuthApiController::class, 'sendEmailVerificationLink'])->middleware(['auth:sanctum', 'throttle:auth-verify-send'])->name('verification.send');
    Route::get('/email/verify/', [AuthApiController::class, 'verifyEmail'])->middleware(['signed', 'throttle:auth-sensitive'])->name('verification.email.confirm');
});
