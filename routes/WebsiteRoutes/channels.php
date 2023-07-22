<?php
//channel routes & creator routes
use App\Http\Controllers\Content\CreatorController;
use App\Http\Controllers\Content\CreatorInteractionController;
use Illuminate\Support\Facades\Route;

Route::get('channel/{creator:slug}', [CreatorController::class,'show'])->name("channel.show");
