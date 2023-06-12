<?php


// studio routes
use App\Http\Controllers\Content\StreamController;
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Tools\ImportingController;
use App\Http\Controllers\Tools\LinkingController;
use App\Http\Controllers\Tools\UnionController;
use App\Http\Controllers\Tools\VideoUploadController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

//add a token to these routes that says studio

Route::middleware('auth')->group(function () {

    Route::get('/studio', function () {
        return Inertia::render('Studio/Dashboard');
    })->name("studio.dashboard");

    Route::get('studio/upload',  [VideoUploadController::class, 'edit'])->name("studio.upload");
    Route::post('studio/upload', [VideoUploadController::class, 'upload'])->name("studio.upload");

    Route::get('studio/video/{video:slug}', [VideoController::class,'edit'])->name("studio.video.edit");
    Route::get('studio/stream/{stream:slug}', [StreamController::class,'edit'])->name("studio.stream.edit");
    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");
    Route::get('import', [ImportingController::class,'index'])->name('studio.importing.index');
    Route::get('import/{platform}', [ImportingController::class,'studio.import']);
    Route::get('import/login/{platform}', [ImportingController::class,'studio.logIn']);
    Route::get('studio/link/{platform}', [LinkingController::class,'studio.link']);
    Route::post('studio/login/{platform}', [LinkingController::class,'studio.logIn']);
});


Route::get('create_account/import/{platform}', [ImportingController::class,'create_account'])->middleware("guest");

//oauth routes
