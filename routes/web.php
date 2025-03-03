<?php


use App\Enums\Platform;
use App\Helpers\JoshPing;
use App\Http\Controllers\WebControllers\SupportWebController;
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

require __DIR__ . '/WebsiteRoutes/auth.php';
require __DIR__ . '/WebsiteRoutes/videos.php';
require __DIR__ . '/WebsiteRoutes/podcasts.php';
require __DIR__ . '/WebsiteRoutes/streams.php';
require __DIR__ . '/WebsiteRoutes/creators.php';
require __DIR__ . '/WebsiteRoutes/studio.php';
require __DIR__ . '/WebsiteRoutes/feed.php';
require __DIR__ . '/WebsiteRoutes/categories.php';
require __DIR__ . '/WebsiteRoutes/search.php';
require __DIR__ . '/WebsiteRoutes/music.php';
require __DIR__ . '/WebsiteRoutes/user.php';
require __DIR__ . '/WebsiteRoutes/chatrooms.php';

Route::get('/equity', function () {
    return Inertia::render('Equity');
})->name('equity');

//admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin', function () { return Inertia::render('Admin/AdminDashboard'); })->name('admin.dashboard');
});

// landing route
Route::get('/', function () {
    return Inertia::render('Viewer/Home/Homepage');
    // return Inertia::render('Viewer/Landing/Landing');
})->name('landing');

//home route
Route::get('/home', [SupportWebController::class,'home'])->name('home');

//landing route
Route::get('/about', [SupportWebController::class, 'about'])->name('about');

//policy and terms
Route::get('/terms_of_service', [SupportWebController::class, 'terms'])->name('terms');
Route::get('/privacy_policy', [SupportWebController::class,'privacy'])->name('privacy');

// marketplace
Route::get('/marketplace', [SupportWebController::class,'marketplace'])->name('marketplace');

if (config('app.env') == 'local') {
    Route::get('/ping', function () {
        return JoshPing::ping();
    });
}

//Route::get('/search-test', function () {
//    $platform = new \App\Helpers\PlatformAPIs\YouTube();
//
////    $searchChannels = $platform->getCreators(['UC-lHJZR3Gqxm24_Vd_AJ5Yw']);
////    dd($searchChannels);
//
////    $searchVideo = $platform->getVideoOrStream(['FafXBaAEowM']);
////    dd($searchVideo);
//
//    $getChannel = $platform->getCreatorVideosBeforeDate('UC-lHJZR3Gqxm24_Vd_AJ5Yw');
//    dd($getChannel);
//
//    $searchQuery = new \App\Helpers\SearchQueryDTO('pewdiepie', 20, [$platform->getPlatform()]);
//    $results = $platform->searchVideos($searchQuery);
//    dd($results);
//
//})->name('search.test');

//Route::get('/colon-test', function () {
//    $tools = new \App\Helpers\Tools();
//    $seconds = $tools->convertColonSeparatedTimeToSeconds('05:01');
//    dd($seconds);
//})->name('colon.test');
//
//Route::get('/rumble-video-test', function () {
//    $rumble = new \App\Helpers\PlatformAPIs\Rumble();
//    $results = $rumble->getVideo('v6pv0j6-rumble-trump-putin-plant.html?e9s=rel_v2_ep');
//})->name('rumble.video.test');
//
//Route::get('/get-embed-link', function () {
//    $rumble = new \App\Helpers\PlatformAPIs\Rumble();
//    $results = $rumble->grabEmbedLink('6nqmlm');
//    return $results;
//})->name('rumble.embed.link');
