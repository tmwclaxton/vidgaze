<?php


// studio routes
use App\Http\Controllers\WebControllers\LinkingWebController;
use App\Http\Controllers\WebControllers\StreamWebController;
use App\Http\Controllers\WebControllers\StudioWebController;
use App\Http\Controllers\WebControllers\UnionWebController;
use App\Http\Controllers\WebControllers\VideoDraftWebController;
use App\Http\Controllers\WebControllers\VideoWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//add a token to these routes that says studio



Route::get('/studio', [StudioWebController::class, 'dashboard'])->name("studio.dashboard");
Route::get('/studio/content', [StudioWebController::class, 'content'])->name("studio.content");
Route::get('/studio/streaming', [StudioWebController::class, 'stream'])->name("studio.streaming");
Route::get('studio/customise', [StudioWebController::class, 'customise'])->name("studio.customise");



//Route::get('studio/video/{video:slug}', [VideoWebController::class,'edit'])->name("studio.video.edit");
//Route::get('studio/stream/{stream:slug}', [StreamWebController::class,'edit'])->name("studio.stream.edit");
Route::get('studio/unionise', [UnionWebController::class,'index'])->name("studio.unionise");

Route::get('studio/link/{platform}', [LinkingWebController::class,'link'])->name('studio.link');

Route::get('studio/upload',  [VideoDraftWebController::class, 'upload'])->name("studio.upload");
Route::get('studio/video-draft/{slug}/edit',  [VideoDraftWebController::class, 'edit'])->name("studio.video.draft.edit");

Route::get('studio/content', function () {
    return Inertia::render('Studio/Content');
})->name('studio.content');
