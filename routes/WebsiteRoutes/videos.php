<?php


//video routes
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Content\VideoDisinterestController;
use App\Http\Controllers\Content\VideoInteractionController;



Route::get('/shorts', [VideoController::class,'shorts'])->name('videos.shorts');


//Route::get('short/{video:slug}', [VideoController::class,'short'])->name("short.show");

//view routes
Route::prefix('watch/{video:slug}')->name('watch.')->group(function () {
    Route::get('/', [VideoController::class, 'show'])->name('show');
    Route::get('{playlist:slug}', [VideoController::class, 'playlist'])->name('playlist');
});


