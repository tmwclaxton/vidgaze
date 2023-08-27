<?php

//user feed routes
use App\Http\Controllers\WebControllers\FeedWebController;
use App\Http\Controllers\WebControllers\PlaylistWebController;
use Illuminate\Support\Facades\Route;

Route::get('/feed/library', [FeedWebController::class, 'library'])->name("feed.library");
Route::get('/feed/subscriptions', [FeedWebController::class, 'subscriptions'])->name("feed.subscriptions");
Route::get('feed/channels', [FeedWebController::class, 'channels'])->name("feed.channels");

Route::get('/feed/watch_later', function () {
    return redirect()->route('playlist.show', ['slug' => 'watch_later']);
})->name("feed.watch-later");

Route::get('/feed/liked_videos', function () {
    return redirect()->route('playlist.show', ['slug' => 'liked_videos']);
})->name("feed.liked-videos");

Route::get('/feed/history', function () {
    return redirect()->route('playlist.show', ['slug' => 'history']);
})->name("feed.history");

Route::get('/playlist/{slug}', [PlaylistWebController::class, 'show'])->name("playlist.show");

