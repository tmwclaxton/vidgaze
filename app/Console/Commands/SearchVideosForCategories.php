<?php

namespace App\Console\Commands;

use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Http\Controllers\Tools\NanoController;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SearchVideosForCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:search-videos-for-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use AI to come up with a prompt for searching for new videos for categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Retrieve oldest last_updated
        $category = Category::whereNull('twitch_category_id')->where('name', '!=', 'VidGaze Picks')->orderBy('updated_at')->first();

        // Initialize the NanoGPT service
        $nanoGPTService = new NanoController();

        // update last_updated
        $category->updated_at = now();
        $category->save();

        try {
            // Prepare category details
            $categoryName = $category->name;
            $categoryTags = json_encode($category->tags_json);

            // Step 1: Ask AI for background/context on the category and tags
            $contextPrompt = "Tell me something interesting about:\n\n"
                . "Category: {$categoryName}\n"
                . "Tags: {$categoryTags}\n\n"
                . "Provide a brief yet insightful summary.";

            $contextResponse = $nanoGPTService->getChatCompletion([
                ['role' => 'system', 'content' => 'You are a creative and informative AI that provides unique overviews of topics'],
                ['role' => 'user', 'content' => $contextPrompt],
            ], 'gemini-2.0-flash-001', [], true);

            // Extract AI-generated context
            $contextText = trim($contextResponse['choices'][0]['message']['content']);

            // Step 2: Use the AI-provided context to generate an engaging search query
            $queryPrompt = "Based on the following category, tags, and additional context, "
                . "generate a creative YouTube search query.\n\n"
                . "Category: {$categoryName}\n"
                . "Tags: {$categoryTags}\n"
                . "Context: {$contextText}\n\n"
                . "Current date: " . now()->format('Y-m-d') . "\n\n"
                . "Return only the optimized search query, without any additional text.";

            $queryResponse = $nanoGPTService->getChatCompletion([
                ['role' => 'system', 'content' => 'You are a YouTube viewer who wants to watch a cool video and has to come up with a short search query.'],
                ['role' => 'user', 'content' => $queryPrompt],
            ], 'gemini-2.0-flash-001',[], true);

            // Extract the AI-generated search query
            $query = trim($queryResponse['choices'][0]['message']['content']);

            if (empty($query)) {
                Log::error("Empty search query generated for category '{$categoryName}'");
                return;
            }

            $searchQuery = $query;
            $query = new SearchQueryDTO($searchQuery, 10);
            Search::searchJobs($query);

            // wait 25 seconds then run searchResults
            sleep(25);
            $results = Search::searchResults($query);

        }
        catch (\Throwable $th) {
            Log::error("Error generating search query for category '{$categoryName}': " . $th->getMessage());
        }
    }

}
