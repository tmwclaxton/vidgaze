<?php

use App\Http\Controllers\WebControllers\PodcastWebController;
use Illuminate\Support\Facades\Route;

Route::get('/podcasts', [PodcastWebController::class, 'index'])->name('podcasts.index');
Route::get('/podcast/{slug}', [PodcastWebController::class, 'show'])->name('podcast.show');
Route::get('/podcast/{podcastSlug}/episode/{episodeSlug}', [PodcastWebController::class, 'episode'])->name('podcast.episode');
