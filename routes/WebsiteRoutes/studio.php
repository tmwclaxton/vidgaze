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

        $sources = [];
            auth()->user()->creator()->with('sources')->first()->sources()->get(['source_name', 'external_channel_id'])->each(
            function ($source) use (&$sources){
                $sources[$source->source_name] = $source->external_channel_id;
            }

        );
        return Inertia::render('Studio/Dashboard',[
            'claimed_platforms' => $sources
        ]);
    })->name("studio.dashboard");



    Route::get('studio/video/{video:slug}', [VideoController::class,'edit'])->name("studio.video.edit");
    Route::get('studio/stream/{stream:slug}', [StreamController::class,'edit'])->name("studio.stream.edit");
    Route::get('studio/unionise', [UnionController::class,'index'])->name("studio.unionise");

});



//oauth routes
