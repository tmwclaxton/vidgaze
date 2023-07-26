<?php

use App\Http\Controllers\Tools\ImportingController;
use App\Http\Controllers\Tools\LinkingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    //    Route::get('import/login/{platform}', [ImportingController::class,'logIn'])->name('studio.logIn');
    Route::get('studio/link/{platform}', [LinkingController::class,'link'])->name('studio.link');
    Route::get('studio/login/{platform}', [LinkingController::class,'logIn'])->name('studio.logIn');

    Route::get('import', [ImportingController::class,'index'])->name('studio.importing.index');
    Route::get('import/{platform}', [ImportingController::class,'import'])->name('studio.import');
    Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

});
