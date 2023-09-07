<?php

use App\Http\Controllers\WebControllers\UserWebController;

Route::get('/profile', [UserWebController::class, 'edit'])->name('profile.edit')->middleware(['auth.flag.cookie']);
