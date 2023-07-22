<?php

use App\Http\Controllers\Content\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('categories/infinite', [CategoryController::class, 'infinite'])->name('categories.infinite');
