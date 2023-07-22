<?php


//podcast routes
use App\Http\Controllers\Content\PodcastController;
use App\Http\Controllers\Content\PodcastInteractionController;

Route::get('/podcasts', [PodCastController::class,'index_page'])->name("podcasts.index.page");
Route::get('/podcast/', [PodCastController::class,'show'])->name("podcast.show");
Route::get('/podcast/episode/', [PodCastController::class,'episode'])->name("podcast.episode");
