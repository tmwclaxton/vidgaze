<?php

use App\Http\Controllers\ApiControllers\CommentApiController;
use App\Http\Controllers\ApiControllers\CommentInteractionApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('/comment')->name('comment')->group(function () {

    Route::get('/index', [CommentApiController::class, 'index'])->name('index');

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/store', [CommentApiController::class, 'store'])->name('store');
        Route::patch('/update', [CommentApiController::class, 'update'])->name('update');
        Route::delete('/destroy', [CommentApiController::class, 'destroy'])->name('destroy');

        // comment interaction routes // check if user is authenticated in a group

        // This allows users to add and remove a like or dislike from a comment
        Route::post('/like', [CommentInteractionApiController::class, 'toggleLike'])
            ->name('like.toggle');

        Route::post('/dislike', [CommentInteractionApiController::class, 'toggleDislike'])
            ->name('dislike.toggle');

        // used for like and dislike button
        Route::get('/interactions', [CommentInteractionApiController::class,"getInteractionsByItem"])->name('interaction');

    });

});
