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
        // Retrieve all categories except 'VidGaze Picks'
        $categories = Category::all()->reject(fn($category) => $category->name === 'VidGaze Picks')->shuffle();

        // Initialize the NanoGPT service
        $nanoGPTService = new NanoController();

        foreach ($categories as $category) {
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
                    ['role' => 'system', 'content' => 'You are an informative AI that provides insightful overviews of topics.'],
                    ['role' => 'user', 'content' => $contextPrompt],
                ], 'chatgpt-4o-latest');

                // Extract AI-generated context
                $contextText = trim($contextResponse['choices'][0]['message']['content']);

                // Step 2: Use the AI-provided context to generate an engaging search query
                $queryPrompt = "Based on the following category, tags, and additional context, "
                    . "generate a natural, trending, and high-relevance YouTube search query "
                    . "that aligns with what people are actively searching for.\n\n"
                    . "Category: {$categoryName}\n"
                    . "Tags: {$categoryTags}\n"
                    . "Context: {$contextText}\n\n"
                    . "Current date: " . now()->format('Y-m-d') . "\n\n"
                    . "Return only the optimized search query, without any additional text.";

                $queryResponse = $nanoGPTService->getChatCompletion([
                    ['role' => 'system', 'content' => 'You are a YouTube viewer who wants to watch a cool video and has to come up with a short search query.'],
                    ['role' => 'user', 'content' => $queryPrompt],
                ], 'chatgpt-4o-latest');

                // Extract the AI-generated search query
                $query = trim($queryResponse['choices'][0]['message']['content']);

                if (empty($query)) {
                    continue;
                }

                // Debugging: Display the generated query (Remove this in production)
//                $searchQuery = $query;
//                $query = new SearchQueryDTO($searchQuery, 10);
//                Search::searchJobs($query);

            } catch (\Throwable $th) {
                Log::error("Error generating search query for category '{$categoryName}': " . $th->getMessage());
            }
        }
    }

}
