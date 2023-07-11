<?php

use App\Http\Controllers\Content\CommentController;
use App\Http\Controllers\Content\CommentInteractionController;
use Illuminate\Support\Facades\Route;

Route::get('/comments/infinite', [CommentController::class, 'infinite'])->name('comments.infinite');
Route::middleware(['auth'])->group(function () {
    Route::post('/comments/store', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/update', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/destroy', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// comment interaction routes // check if user is authenticated in a group
Route::middleware(['auth'])->group(function () {

    // This allows users to add and remove a like or dislike from a comment.
    Route::post('/comment/{commentId}/like', [CommentInteractionController::class, 'toggleLike'])
        ->name('comment.like.toggle');

    Route::post('/comment/{commentId}/dislike', [CommentInteractionController::class, 'toggleDislike'])
        ->name('comment.dislike.toggle');

    // used for like and dislike button
    Route::get('/comment/interactions', [CommentInteractionController::class,"getInteractionsByItem"])->name('comment.interactions');

});
