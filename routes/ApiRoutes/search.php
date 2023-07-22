<?php

use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Search\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search_query', [SearchController::class, 'get'])->name('search_query');


Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchBarController::class, 'get'])->name('search_suggestions');
});
