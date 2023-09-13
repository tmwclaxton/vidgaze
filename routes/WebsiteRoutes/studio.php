<?php
// studio routes
use App\Http\Controllers\WebControllers\LinkingWebController;
use App\Http\Controllers\WebControllers\StreamWebController;
use App\Http\Controllers\WebControllers\StudioWebController;
use App\Http\Controllers\WebControllers\UnionWebController;
use App\Http\Controllers\WebControllers\VideoDraftWebController;
use App\Http\Controllers\WebControllers\VideoWebController;
use Illuminate\Support\Facades\Route;

Route::prefix('studio')->name('studio.')->middleware(['auth.flag.cookie'])->group(function () {
    Route::get('/', [StudioWebController::class, 'dashboard'])->name("dashboard");
    Route::get('/content', [StudioWebController::class, 'content'])->name("content");
    Route::get('/streaming', [StudioWebController::class, 'stream'])->name("streaming");
    Route::get('unionise', [UnionWebController::class,'index'])->name("unionise");
    Route::get('customise', [StudioWebController::class, 'customise'])->name("customise");
    Route::get('link/{platform}', [LinkingWebController::class,'link'])->name('link');
    Route::get('upload',  [VideoDraftWebController::class, 'upload'])->name("upload");
    Route::get('video-draft/{slug}/edit',  [VideoDraftWebController::class, 'edit'])->name("video.draft.edit");
    Route::get('video/{slug}/edit',  [VideoWebController::class, 'edit'])->name("video.edit");
    Route::get('stream/{slug}/edit',  [StreamWebController::class, 'edit'])->name("stream.edit");
});
