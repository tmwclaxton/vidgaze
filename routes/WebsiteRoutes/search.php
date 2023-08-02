<?php





//search routes

use App\Http\Controllers\WebControllers\SearchWebController;
use Illuminate\Support\Facades\Route;


Route::get('/search', [SearchWebController::class, 'index'])->name('search');



