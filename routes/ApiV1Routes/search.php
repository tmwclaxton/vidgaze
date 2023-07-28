<?php


use App\Http\Controllers\ApiControllers\SearchApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->name('search.')->group(function () {

    Route::get('/query', [SearchApiController::class, 'getSearchResults'])->name('query');

    Route::middleware('throttle:60,1')->group(function () {
        //search bar
        Route::get('/suggestions', [SearchApiController::class, 'getSearchSuggestions'])->name('suggestions');
    });

});
