<?php

use App\Http\Controllers\ApiControllers\CategoryApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('/category')->name('category.')->group(function () {


    Route::get('/index', [CategoryApiController::class, 'index'])->name('index');
    Route::get('/{slug}', [CategoryApiController::class, 'show'])->name('show');

});
