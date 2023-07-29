<?php

//music routes
use App\Http\Controllers\WebControllers\MusicWebController;
use Illuminate\Support\Facades\Route;

Route::get('/music', [MusicWebController::class,'index'])->name('music.index');
//Route::get('/music/album', [MusicController::class,'album'])->name('music.album');
//Route::get('/music/category', [MusicController::class,'category'])->name('music.category');
//Route::get('/music/track', [MusicController::class,'track'])->name('music.track');
