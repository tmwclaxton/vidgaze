<?php

use App\Http\Controllers\ApiControllers\CategoryApiController;
use Illuminate\Support\Facades\Route;

Route::get('categories/infinite', [CategoryApiController::class, 'infinite'])->name('categories.infinite');
