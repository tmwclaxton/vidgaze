<?php

//music routes
use App\Http\Controllers\Content\MusicController;
use Illuminate\Support\Facades\Route;

Route::get('/music', [MusicController::class,'index'])->name('music.index');
//Route::get('/music/album', [MusicController::class,'album'])->name('music.album');
//Route::get('/music/category', [MusicController::class,'category'])->name('music.category');
//Route::get('/music/track', [MusicController::class,'track'])->name('music.track');
