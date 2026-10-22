<?php
// Auth
use App\Http\Controllers\ApiControllers\AuthApiController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthApiController::class, 'signup'])->middleware('throttle:5,1')->name('register');
    Route::post('login', [AuthApiController::class, 'login'])->middleware('throttle:10,1')->name('login');
    Route::post('logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

    Route::get('user', [AuthApiController::class, 'getAuthenticatedUser'])->middleware('auth:sanctum')->name('user');


    Route::post('/password/email', [AuthApiController::class, 'sendPasswordResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::post('/password/reset', [AuthApiController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.reset');
    Route::post('/password/confirm', [AuthApiController::class, 'confirmPassword'])->middleware(['auth:sanctum', 'throttle:10,1'])->name('password.confirm');
    Route::patch('/password/update', [AuthApiController::class, 'updatePassword'])->middleware(['auth:sanctum', 'throttle:10,1'])->name('password.change');


    // check token privileges
    Route::get('/token/privileges', [AuthApiController::class, 'checkTokenPrivileges'])->middleware('auth:sanctum')->name('token.privileges');
    // get updated token
    Route::get('/token/refresh', [AuthApiController::class, 'refreshToken'])->middleware('auth:sanctum')->name('token.refresh');

    // override email verification route names like remove the prefixes
    Route::post('/email/verify/', [AuthApiController::class, 'sendEmailVerificationLink'])->middleware(['auth:sanctum', 'throttle:5,1'])->name('verification.send');
    Route::get('/email/verify/', [AuthApiController::class, 'verifyEmail'])->middleware(['signed', 'throttle:10,1'])->name('verification.email.confirm');

    // toggle shorts
    Route::patch('/shorts/toggle', [AuthApiController::class, 'toggleShorts'])->middleware('auth:sanctum')->name('shorts.toggle');
});
