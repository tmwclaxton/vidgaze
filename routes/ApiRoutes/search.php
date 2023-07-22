<?php


use App\Http\Controllers\ApiControllers\SearchApiController;
use Illuminate\Support\Facades\Route;

Route::get('/search_query', [SearchApiController::class, 'get'])->name('search_query');


Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchApiController::class, 'get'])->name('search_suggestions');
});
