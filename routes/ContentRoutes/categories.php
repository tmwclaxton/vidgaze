<?php

//category routes
use App\Http\Controllers\Content\CategoryController;

Route::get('category/{category:slug}', [CategoryController::class,'show'])->name("category.show");
Route::get('categories', [CategoryController::class,'index'])->name("categories.index"); //used by carousel on stream page
Route::get('categories/infinite', [CategoryController::class, 'infinite'])->name('categories.infinite');

