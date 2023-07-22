<?php

//category routes
use App\Http\Controllers\Content\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('category/{category:slug}', [CategoryController::class,'show'])->name("category.show");
Route::get('categories', [CategoryController::class,'index'])->name("categories.index"); //used by carousel on stream page


