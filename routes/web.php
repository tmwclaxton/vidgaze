<?php


use App\Enums\Platform;
use App\Helpers\JoshPing;
use App\Helpers\ResultDTO;
use App\Http\Controllers\Tools\NanoController;
use App\Http\Controllers\WebControllers\SupportWebController;
use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;

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

Route::get('/nano-gpt/test', function () {
    // Grab 100 videos without a category_id
    $videos = Video::whereNull('category_id')->where('categorised', '=', false)->inRandomOrder()->take(20)->get();
    // Grab all categories
    $categories = Category::all();
    $categoryNames = $categories->pluck('name')->toArray();

    // Create an instance of the NanoGPT service
    $nanoGPTService = new NanoController();

    $count = [
        'videos' => $videos->count(),
        'categorized' => 0,
        'pinned' => 0,
        'skipped' => 0,
        'errored' => 0,
    ];
    foreach ($videos as $video) {
        try {
            // AI prompt for categorization
            $prompt = "Based on the following video details, suggest the best fitting category from this list: "
                . json_encode($categoryNames) . "\n\n"
                . "Title: {$video->title}\n"
                . "Description: {$video->description}\n"
                . "Creator: {$video->creator()->first()->name}\n\n"
                . "Return only the category name or 'null' if no category matches.";

            // Generate AI response using NanoGPT
            $response = $nanoGPTService->getChatCompletion([
                ['role' => 'system', 'content' => 'You are a helpful AI assistant that accurately identifies the best category for videos.'],
                ['role' => 'user', 'content' => $prompt],
            ], 'qwen-plus');

            // Get category name from AI response
            $categoryName = trim($response['choices'][0]['message']['content']);

            // If the AI response is 'null' or invalid, mark it in Redis and skip
            if ($categoryName === 'null' || !in_array($categoryName, $categoryNames)) {
                $count['skipped']++;
                $video->categorised = true;
                $video->categorised_at = Carbon::now();
                $video->save();
                continue;
            }

            // Retrieve the category ID based on the category name
            $category = $categories->firstWhere('name', $categoryName);
            if ($category) {
                // Update the video with the determined category_id
                $video->update(['category_id' => $category->id]);

                // If the video is less than 1 week old, pin it
                if (Carbon::parse($video->time_published)->diffInDays(now()) < 7) {
                    $video->pinned = true;
                    $video->pin_expires_at = now()->addDays(3);
                    $count['pinned']++;
                }

                $count['categorized']++;

                $video->categorised = true;
                $video->categorised_at = Carbon::now();
                $video->save();
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            logger()->error('Error categorizing video ID ' . $video->id . ': ' . $e->getMessage());
            $count['errored']++;
            continue;
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Videos have been categorized.',
        'count' => $count,
    ]);
});

//Route::get('/search-test', function () {
//    $platform = new \App\Helpers\PlatformAPIs\Vimeo();
//    $results = $platform->getFeaturedVideos();
//
////    ResultDTO::saveAll($results)
//
//    $savedResults = ResultDTO::saveAll($results);
//
//    // iterate through and changed pinned to true and pin_expires_at to 1 week from now
//    foreach ($savedResults as $result) {
//        $result->pinned = true;
//        $result->pin_expires_at = now()->addWeek();
//        $result->save();
//    }

//    $searchQuery = new \App\Helpers\SearchQueryDTO('pewdiepie', 20, [$platform->getPlatform()]);
//    $results = $platform->search($searchQuery);
//    dd($results);

//    $searchCreators = $platform->searchCreators($searchQuery);
//    dd($searchCreators);
//    $searchChannels = $platform->getCreators(['UC-lHJZR3Gqxm24_Vd_AJ5Yw']);
//    dd($searchChannels);
//
//    $searchVideo = $platform->getVideoOrStream(['FafXBaAEowM']);
//    dd($searchVideo);
//
//    $getChannel = $platform->getCreatorVideosBeforeDate('UC-lHJZR3Gqxm24_Vd_AJ5Yw');
//    dd($getChannel);
//
//    $results = $platform->searchVideos($searchQuery);
//    dd($results);

//})->name('search.test');

