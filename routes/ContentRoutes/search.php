<?php





//search routes
use App\Http\Controllers\Search\SearchController;

Route::get('/search/', [SearchController::class, 'get']);
Route::middleware('throttle:60,1')->group(function () {
    //search bar
    Route::get('/search_suggestions', [SearchBarController::class, 'get'])->name('search_suggestions');
});


