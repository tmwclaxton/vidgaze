<?php

// verify email page
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', function () {
    return "Page not built yet";
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');
