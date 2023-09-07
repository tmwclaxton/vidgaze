<?php

// verify email page
use App\Http\Controllers\WebControllers\AuthWebController;


    Route::get('register', [AuthWebController::class, 'register'])
                ->name('register');

    Route::get('login', [AuthWebController::class, 'login'])
                ->name('login');

    Route::get('forgot-password', [AuthWebController::class, 'forgotPassword'])
                ->name('password.request');

    Route::get('reset-password/{token}', [AuthWebController::class, 'resetPassword'])
                ->name('password.reset');

    Route::get('verify-email', [AuthWebController::class, 'verifyEmail'])
                ->name('verification.notice')->middleware(['auth.flag.cookie']);


    Route::get('/email/verify/{id}/{hash}', [AuthWebController::class, 'VerifyEmailRedirect'])
        ->middleware(['signed'])->name('verification.verify');

    Route::get('confirm-password', [AuthWebController::class, 'confirmPassword'])
                ->name('password.confirm');

    //Route to add or overwrite auth_flag cookie without changing page
    Route::get('auth-flag/{flag}', function ($flag) {
        return response()->json([
            'auth_flag' => $flag
        ])->cookie('auth_flag', $flag, 60);
    })->name('auth.flag');
