<?php


//video routes


use App\Http\Controllers\WebControllers\VideoWebController;
use Illuminate\Support\Facades\Route;

Route::get('/shorts', [VideoWebController::class,'shorts'])->name('videos.shorts');

//view routes
Route::prefix('watch/{slug}')->name('watch.')->group(function () {
    Route::get('/', [VideoWebController::class, 'show'])->name('show');
});


