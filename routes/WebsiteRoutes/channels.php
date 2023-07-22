<?php
//channel routes & creator routes
use App\Http\Controllers\WebControllers\CreatorWebController;
use Illuminate\Support\Facades\Route;

Route::get('channel/{creator:slug}', [CreatorWebController::class,'show'])->name("channel.show");
