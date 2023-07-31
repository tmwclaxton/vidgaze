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
                ->name('verification.notice');


    Route::get('/email/verify/{id}/{hash}', [AuthWebController::class, 'VerifyEmailRedirect'])
        ->middleware(['signed'])->name('verification.verify');

    Route::get('confirm-password', [AuthWebController::class, 'confirmPassword'])
                ->name('password.confirm');

