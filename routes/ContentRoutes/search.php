<?php





//search routes
use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Search\SearchController;


Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/search_query', [SearchController::class, 'get'])->name('search_query');


Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchBarController::class, 'get'])->name('search_suggestions');
});


