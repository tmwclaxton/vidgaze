<?php


//stream routes
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\StreamInteractionController;

Route::get('/livestreams', [StreamController::class,'index'])->name("streams.index");
Route::get('/streams/top', [StreamController::class,'topStreams'])->name("streams.top");
Route::get('/stream/{stream:slug}', [StreamController::class,'show'])->name("stream.show");


