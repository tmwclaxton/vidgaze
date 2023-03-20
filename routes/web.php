<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoUploadController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Home/Home', [
        'showingStudioLinks' => false,
    ]);
});
Route::get('/home', function () {
    return Inertia::render('Home/Home');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// studio routes
Route::middleware('auth')->group(function () {
    Route::get('/studio', function () {
        return Inertia::render('Studio/Dashboard', [
            'sources' => auth()->user()->creator->sources,
            'showingStudioLinks' => true,

        ]);
    })->name("studio.dashboard")->middleware("auth");



//    Route::get('studio/upload',  [VideoUploadController::class, 'show'])->name("upload.show");
//    Route::post('studio/upload', [VideoUploadController::class, 'upload'])->name("upload.upload");
//
//    Route::get('studio/customise', [CreatorController::class,'edit'])->name("studio.customise.edit")->middleware(['auth']);
//    Route::post('studio/customise', [CreatorController::class,'update'])->name("studio.customise.update")->middleware(['auth']);
//    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio/unionise")->middleware("auth");
//    Route::get('studio/link/{platform}', [LinkingController::class,'link'])->middleware("auth");
//    Route::post('studio/login/{platform}', [LinkingController::class,'logIn'])->middleware("auth");
//


//    Route::get('studio/video/{video:slug}', [VideoController::class,'edit'])->name("studio.video.edit");
//    Route::get('studio/stream/{stream:slug}', [StreamController::class,'edit'])->name("studio.stream.edit");

});




Route::post('/test', function() {
    return redirect()->back()->with('toast', 'Toast endpoint!');
});


require __DIR__.'/auth.php';
