<?php


// studio routes
use App\Http\Controllers\WebControllers\LinkingWebController;
use App\Http\Controllers\WebControllers\StreamWebController;
use App\Http\Controllers\WebControllers\VideoDraftWebController;
use App\Http\Controllers\WebControllers\VideoWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//add a token to these routes that says studio



Route::get('/studio', function () {
    return Inertia::render('Studio/Dashboard');
})->name("studio.dashboard");

Route::get('studio/video/{video:slug}', [VideoWebController::class,'edit'])->name("studio.video.edit");
Route::get('studio/stream/{stream:slug}', [StreamWebController::class,'edit'])->name("studio.stream.edit");
//    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");

Route::get('studio/link/{platform}', [LinkingWebController::class,'link'])->name('studio.link');

Route::get('studio/upload',  [VideoDraftWebController::class, 'upload'])->name("studio.upload");
Route::get('studio/video/{slug}/edit',  [VideoDraftWebController::class, 'edit'])->name("studio.video.draft.edit");
Route::put('studio/video/{slug}',  [VideoDraftWebController::class, 'update'])->name("studio.video.update");
Route::post('studio/video/{slug}/publish',  [VideoDraftWebController::class, 'publish'])->name("studio.video.publish");



//oauth routes
