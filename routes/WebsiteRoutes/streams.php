<?php


//stream routes
use App\Http\Controllers\WebControllers\StreamWebController;
use Illuminate\Support\Facades\Route;

Route::get('/livestreams', [StreamWebController::class,'index'])->name("streams.index");

Route::get('/stream/{stream:slug}', [StreamWebController::class,'show'])->name("stream.show");


