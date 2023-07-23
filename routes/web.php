<?php


use App\Http\Controllers\WebControllers\SupportWebController;
use Illuminate\Support\Facades\Route;

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

require __DIR__ . '/WebsiteRoutes/videos.php';
require __DIR__ . '/WebsiteRoutes/comments.php';
require __DIR__ . '/WebsiteRoutes/podcasts.php';
require __DIR__ . '/WebsiteRoutes/streams.php';
require __DIR__ . '/WebsiteRoutes/channels.php';
require __DIR__ . '/WebsiteRoutes/studio.php';
require __DIR__ . '/WebsiteRoutes/feed.php';
require __DIR__ . '/WebsiteRoutes/categories.php';
require __DIR__ . '/WebsiteRoutes/search.php';
require __DIR__ . '/WebsiteRoutes/music.php';





// landing route
Route::get('/', function () {
    if (auth()->user() === null) {
        return redirect()->route('about');
    } else {
        return redirect()->route('home');
    }
})->name('landing');

//home route
Route::get('/home', [SupportWebController::class,'home'])->name('home');
//landing route
Route::get('/about', [SupportWebController::class, 'about'])->name('about');
//policy and terms
Route::get('/terms_of_service', [SupportWebController::class, 'terms'])->name('terms');
Route::get('/privacy_policy', [SupportWebController::class,'privacy'])->name('privacy');

//Route::get('/support', [SupportController::class,'support'])->name('support');
// support email route
//Route::post('/support', [SupportController::class,'sendSupportEmail'])->name('support.email.send');






