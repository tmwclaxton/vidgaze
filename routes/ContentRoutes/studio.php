<?php


// studio routes
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Tools\ImportingController;
use App\Http\Controllers\Tools\LinkingController;
use App\Http\Controllers\Tools\UnionController;
use App\Http\Controllers\Tools\VideoUploadController;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('/studio', function () {
        return Inertia::render('Studio/Dashboard', [
            'sources' => auth()->user()->creator->sources,
            'showingStudioLinks' => true,
        ]);
    })->name("studio.dashboard") ;
    Route::get('studio/upload',  [VideoUploadController::class, 'edit'])->name("studio.upload");
    Route::post('studio/upload', [VideoUploadController::class, 'upload']);
    Route::get('studio/video/{video:slug}', [VideoController::class,'edit'])->name("studio.video.edit");
    Route::get('studio/stream/{stream:slug}', [StreamController::class,'edit'])->name("studio.stream.edit");
    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");
    Route::get('import', [ImportingController::class,'index'])->name('importing.index');
    Route::get('import/{platform}', [ImportingController::class,'import']);
    Route::get('import/login/{platform}', [ImportingController::class,'logIn']);
    Route::get('studio/link/{platform}', [LinkingController::class,'link']);
    Route::post('studio/login/{platform}', [LinkingController::class,'logIn']);
});
Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

//oauth routes
