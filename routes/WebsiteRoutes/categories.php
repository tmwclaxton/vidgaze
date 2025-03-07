<?php

//category routes
use App\Http\Controllers\WebControllers\CategoryWebController;
use Illuminate\Support\Facades\Route;

Route::get('category/{slug}', [CategoryWebController::class,'show'])->name("category.show");
Route::get('categories', [CategoryWebController::class,'index'])->name("category.index");


