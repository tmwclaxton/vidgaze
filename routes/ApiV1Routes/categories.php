<?php

use App\Http\Controllers\ApiControllers\CategoryApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('/category')->name('category.')->middleware('throttle:60,1')->group(function () {


    Route::get('/grabStreamCategories', [CategoryApiController::class, 'grabStreamCategories'])->name('index.streams');
    Route::get('/grabVideoCategories', [CategoryApiController::class, 'grabVideoCategories'])->name('index.videos');
    Route::get('/{slug}', [CategoryApiController::class, 'show'])->name('show');

});
