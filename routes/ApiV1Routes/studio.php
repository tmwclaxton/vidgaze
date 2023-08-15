<?php

use App\Http\Controllers\ApiControllers\LinkingApiController;
use App\Http\Controllers\ApiControllers\VideoDraftApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
//    Route::get('import', [ImportingController::class,'index'])->name('studio.importing.index');
//    Route::get('import/{platform}', [ImportingController::class,'import'])->name('studio.import');
//    Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

    Route::post('studio/video/{slug}/upload', [VideoDraftApiController::class, 'upload'])->name("studio.video.upload");
    Route::post('studio/video/prime', [VideoDraftApiController::class, 'primeNewVideoDraft'])->name("studio.video.prime");

    Route::get('studio/login/{platform}', [LinkingApiController::class, 'logIn'])->name('studio.login');
    Route::get('studio/link/{platform}', [LinkingApiController::class, 'link'])->name('studio.link');

    Route::get('my/creator/sources', [LinkingApiController::class,'myCreatorSources'])->name('my.creator.sources');
});
