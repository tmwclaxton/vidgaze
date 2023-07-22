<?php

//category routes
use App\Http\Controllers\WebControllers\CategoryWebController;
use Illuminate\Support\Facades\Route;

Route::get('category/{category:slug}', [CategoryWebController::class,'show'])->name("category.show");
Route::get('categories', [CategoryWebController::class,'index'])->name("categories.index"); //used by carousel on stream page


