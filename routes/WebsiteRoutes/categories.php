<?php

//category routes
use App\Http\Controllers\WebControllers\CategoryWebController;
use Illuminate\Support\Facades\Route;

Route::get('category/{category:slug}', [CategoryWebController::class,'show'])->name("category.show");


