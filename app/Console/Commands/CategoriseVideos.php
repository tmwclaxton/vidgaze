<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tools\NanoController;
use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CategoriseVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:categorise-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Use AI to categorise videos based on their title and description";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Grab 100 videos without a category_id
        $videos = Video::whereNull('category_id')->where('categorised', '=', false)->inRandomOrder()->take(20)->get();
        // Grab all categories
        $categories = Category::all();
        $categoryNames = $categories->pluck('name')->toArray();

        //remove "VidGaze Picks" from the list of categories
        $categoryNames = array_diff($categoryNames, ['VidGaze Picks']);

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
//                    . "If the video is about Unionisation, Freedom of Speech or XMR please categorize it as 'VidGaze Picks'.\n\n"
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
                    if (Carbon::parse($video->time_published)->diffInDays(now()) < 28) {
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

        // return cmd line info



    }
}
