<?php


use App\Http\Controllers\ChatRoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('/chatroom')->name('chatroom.')->group(function () {
    // list all chatrooms
    Route::get('/global-chat', [ChatRoomController::class, 'globalChat'])->name('show');
});
