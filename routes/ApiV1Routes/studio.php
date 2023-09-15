<?php

use App\Http\Controllers\ApiControllers\LinkingApiController;
use App\Http\Controllers\ApiControllers\StudioContentApiController;
use App\Http\Controllers\ApiControllers\VideoDraftApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('studio.')->prefix('/studio')->group(function () {
//    Route::get('import', [ImportingController::class,'index'])->name('importing.index');
//    Route::get('import/{platform}', [ImportingController::class,'import'])->name('import');
//    Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

    Route::post('video/prime', [VideoDraftApiController::class, 'primeNewVideoDraft'])->name("video.prime");
    Route::post('video/{slug}/upload', [VideoDraftApiController::class, 'upload'])->name("video.upload");

    Route::get('video/draft/{slug}/edit', [VideoDraftApiController::class, 'edit'])->name("video.draft.edit");
    Route::patch('video/draft/{slug}/update', [VideoDraftApiController::class, 'update'])->name("video.draft.update");
    Route::post('video/draft/{slug}/publish',  [VideoDraftApiController::class, 'publish'])->name("video.draft.publish");

    Route::get('video/draft/{slug}/edit', [VideoDraftApiController::class, 'edit'])->name("video.draft.edit");
    Route::patch('video/draft/{slug}/update', [VideoDraftApiController::class, 'update'])->name("video.draft.update");
    Route::patch('video/draft/{slug}/update/thumbnail', [VideoDraftApiController::class, 'updateThumbnail'])->name("video.draft.thumbnail.update");

    Route::get('login/{platform}', [LinkingApiController::class, 'logIn'])->name('login');
    Route::get('link/{platform}', [LinkingApiController::class, 'link'])->name('link');
    Route::delete('unlink/{platform}', [LinkingApiController::class, 'unlink'])->name('unlink');

    Route::get('content', [StudioContentApiController::class, 'content'])->name('content');
    Route::get('latest/video', [StudioContentApiController::class, 'latestVideo'])->name('latest.video');
    Route::get('video/analytic', [StudioContentApiController::class, 'videoAnalytic'])->name('video.analytic');

    Route::get('analytics', [StudioContentApiController::class, 'analytics'])->name('analytics');
    Route::get('comments', [StudioContentApiController::class, 'comments'])->name('comments');

});
