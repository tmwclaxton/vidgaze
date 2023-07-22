<?php


//video routes


use App\Http\Controllers\WebControllers\VideoWebController;
use Illuminate\Support\Facades\Route;

Route::get('/shorts', [VideoWebController::class,'shorts'])->name('videos.shorts');


//Route::get('short/{video:slug}', [VideoController::class,'short'])->name("short.show");

//view routes
Route::prefix('watch/{video:slug}')->name('watch.')->group(function () {
    Route::get('/', [VideoWebController::class, 'show'])->name('show');
    Route::get('{playlist:slug}', [VideoWebController::class, 'playlist'])->name('playlist');
});


