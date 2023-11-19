<?php


use App\Http\Controllers\ApiControllers\SearchApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('search')->name('search.')->group(function () {

    Route::post('/start_query', [SearchApiController::class, 'startQuery'])->name('start_query');
    Route::get('/get_results', [SearchApiController::class, 'getResults'])->name('get_results');

    Route::middleware('throttle:60,1')->group(function () {
        //search bar
        Route::get('/suggestions', [SearchApiController::class, 'getSearchSuggestions'])->name('suggestions');
    });

});
