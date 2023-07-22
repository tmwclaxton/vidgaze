<?php

use App\Http\Controllers\ApiControllers\CommentApiController;
use App\Http\Controllers\ApiControllers\CommentInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::get('/comments/infinite', [CommentApiController::class, 'infinite'])->name('comments.infinite');
Route::middleware(['auth'])->group(function () {
    Route::post('/comments/store', [CommentApiController::class, 'store'])->name('comments.store');
    Route::put('/comments/update', [CommentApiController::class, 'update'])->name('comments.update');
    Route::delete('/comments/destroy', [CommentApiController::class, 'destroy'])->name('comments.destroy');
});

// comment interaction routes // check if user is authenticated in a group
Route::middleware(['auth'])->group(function () {

    // This allows users to add and remove a like or dislike from a comment.
    Route::post('/comment/{commentId}/like', [CommentInteractionApiController::class, 'toggleLike'])
        ->name('comment.like.toggle');

    Route::post('/comment/{commentId}/dislike', [CommentInteractionApiController::class, 'toggleDislike'])
        ->name('comment.dislike.toggle');

    // used for like and dislike button
    Route::get('/comment/interactions', [CommentInteractionApiController::class,"getInteractionsByItem"])->name('comment.interactions');

});
