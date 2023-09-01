<?php


use App\Http\Controllers\ApiControllers\UnionApiController;


// route group for union routes
Route::prefix('union')->name('union.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('index', [UnionApiController::class, 'index'])->name('index');
    Route::post('join', [UnionApiController::class, 'join'])->name('join');
    Route::post('leave', [UnionApiController::class, 'leave'])->name('leave');
    //Route::post('create', [UnionApiController::class, 'create'])->name('create');
    //Route::delete('delete', [UnionApiController::class, 'delete'])->name('delete');
    //Route::patch('update', [UnionApiController::class, 'update'])->name('update');

});
