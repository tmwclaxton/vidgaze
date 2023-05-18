<?php


//video routes
use App\Http\Controllers\Content\VideoController;
use App\Http\Controllers\Content\VideoDisinterestController;


Route::get('/videos',[VideoController::class,'index'])->name('videos.index');
Route::get('/videos/infinite', [VideoController::class, 'infinite'])->name('videos.infinite');
Route::get('/shorts', [VideoController::class,'shorts'])->name('videos.shorts');
Route::get('short/{video:slug}', [VideoController::class,'short'])->name("short.show");

//view routes
Route::prefix('watch/{video:slug}')->name('watch.')->group(function () {
    Route::get('/', [VideoController::class, 'show'])->name('show');
    Route::get('{playlist:slug}', [VideoController::class, 'playlist'])->name('playlist');
    Route::get('{playlist:slug}/shuffle', [VideoController::class, 'shuffle'])->name('playlist.shuffle');
});

// modal routes //throttle to 30 requests per minute
Route::middleware(['throttle:30,1','auth'])->group(function () {

    // This allows users to create and destroy VideoDisinterest records, indicating that they are not interested in a particular video.
    Route::post('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'create'])
        ->name('video.disinterest.create');

    Route::delete('/videos/{videoId}/disinterest', [VideoDisinterestController::class, 'destroy'])
        ->name('video.disinterest.destroy');

    // This allows users to add and remove a like or dislike from a video.
    Route::post('/videos/{videoId}/like', [VideoController::class, 'like'])
        ->name('video.like.toggle');

    Route::post('/videos/{videoId}/dislike', [VideoController::class, 'dislike'])
        ->name('video.dislike.toggle');


    // this get the details of a video for the content modal or viewing the video or short
    Route::get('/videos/{videoId}/details', [VideoController::class,"modalDetails"])->name('videos.details');
    Route::get('/videos/{videoId}/view_info', [VideoController::class,"getVideoViewInfo"])->name('videos.view.info');


});

Route::post('/videos/{id}/report', [VideoController::class, 'report'])
    ->name('video.report.add')
    ->middleware('throttle:555,15');
