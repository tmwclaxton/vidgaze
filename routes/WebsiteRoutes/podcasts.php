<?php


//podcast routes

use App\Http\Controllers\WebControllers\PodcastWebController;
use Illuminate\Support\Facades\Route;

Route::get('/podcasts', [PodCastWebController::class,'index'])->name("podcasts.index");
Route::get('/podcast/', [PodCastWebController::class,'show'])->name("podcast.show");
Route::get('/podcast/episode/', [PodCastWebController::class,'episode'])->name("podcast.episode");
