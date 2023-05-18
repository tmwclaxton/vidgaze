<?php


//stream routes
use App\Http\Controllers\Content\StreamController;

Route::get('/livestreams', [StreamController::class,'index'])->name("streams.index");
Route::get('/streams/top', [StreamController::class,'topStreams'])->name("streams.top");
Route::get('/stream/{stream:slug}', [StreamController::class,'show'])->name("stream.show");
Route::post('/streams/{id}/report', [StreamController::class, 'report'])
    ->name('stream.report.add')
    ->middleware('throttle:555,15');

