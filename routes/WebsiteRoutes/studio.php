<?php


// studio routes
use App\Http\Controllers\WebControllers\StreamWebController;
use App\Http\Controllers\WebControllers\VideoDraftWebController;
use App\Http\Controllers\WebControllers\VideoWebController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

//add a token to these routes that says studio



Route::get('/studio', function () {
    $sources = [];
//    auth()->user()->creator()->with('sources')->first()->sources()->get(['source_name', 'external_channel_id'])->each(
//        function ($source) use (&$sources){
//            $sources[$source->source_name] = $source->external_channel_id;
//        }
//
//    );
    return Inertia::render('Studio/Dashboard',[
        'claimed_platforms' => $sources
    ]);
})->name("studio.dashboard");
Route::get('studio/video/{video:slug}', [VideoWebController::class,'edit'])->name("studio.video.edit");
Route::get('studio/stream/{stream:slug}', [StreamWebController::class,'edit'])->name("studio.stream.edit");
//    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");

Route::get('studio/upload',  [VideoDraftWebController::class, 'upload'])->name("studio.upload");
Route::get('studio/video/{slug}/edit',  [VideoDraftWebController::class, 'edit'])->name("studio.video.draft.edit");
Route::put('studio/video/{slug}',  [VideoDraftWebController::class, 'update'])->name("studio.video.update");
Route::post('studio/video/{slug}/publish',  [VideoDraftWebController::class, 'publish'])->name("studio.video.publish");



//oauth routes
