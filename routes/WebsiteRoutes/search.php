<?php





//search routes
use App\Http\Controllers\Search\SearchBarController;
use App\Http\Controllers\Search\SearchController;
use Illuminate\Support\Facades\Route;


Route::get('/search', [SearchController::class, 'index'])->name('search');



