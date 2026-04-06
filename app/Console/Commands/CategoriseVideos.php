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
    protected $description = 'Use AI to categorise videos based on their title and description';

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

        // remove "VidGaze Picks" from the list of categories
        $categoryNames = array_diff($categoryNames, ['VidGaze Picks']);

        // Create an instance of the NanoGPT service
        $nanoGPTService = new NanoController;

        $count = [
            'videos' => $videos->count(),
            'categorized' => 0,
            'pinned' => 0,
            'skipped' => 0,
            'errored' => 0,
        ];
        $minConfidence = (float) config('vidgaze.categorisation_min_confidence', 0.55);

        foreach ($videos as $video) {
            try {
                $creatorName = optional($video->creator()->first())->name ?? 'Unknown';

                $prompt = 'Based on the following video details, suggest the best fitting category from this list: '
                    .json_encode(array_values($categoryNames))."\n\n"
                    ."Title: {$video->title}\n"
                    ."Description: {$video->description}\n"
                    ."Creator: {$creatorName}\n\n"
                    .'Return ONLY JSON: {"category":"<exact category name from the list or null>","confidence":0.0} '
                    .'confidence is 0-1. Use null when no category fits well.';

                $response = $nanoGPTService->getChatCompletion([
                    ['role' => 'system', 'content' => 'You classify videos into one editorial category. Reply with JSON only, no markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ], 'qwen-plus', ['max_tokens' => 120], false);

                $raw = trim($response['choices'][0]['message']['content'] ?? '');
                $parsed = $this->parseCategoryAiResponse($raw);
                $categoryName = $parsed['category'];
                $confidence = $parsed['confidence'];

                if ($categoryName === null
                    || ! in_array($categoryName, $categoryNames, true)
                    || $confidence < $minConfidence) {
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
                logger()->error('Error categorizing video ID '.$video->id.': '.$e->getMessage());
                $count['errored']++;

                continue;
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array{category: ?string, confidence: float}
     */
    private function parseCategoryAiResponse(string $raw): array
    {
        $t = preg_replace('/^```json\s*|\s*```$/i', '', trim($raw));
        $decoded = json_decode($t, true);
        if (is_array($decoded) && (isset($decoded['category']) || array_key_exists('category', $decoded))) {
            $cat = $decoded['category'];
            if ($cat === null || (is_string($cat) && strtolower($cat) === 'null')) {
                return ['category' => null, 'confidence' => 0.0];
            }

            return [
                'category' => is_string($cat) ? $cat : null,
                'confidence' => isset($decoded['confidence']) ? (float) $decoded['confidence'] : 0.0,
            ];
        }

        if (strtolower($t) === 'null' || $t === '') {
            return ['category' => null, 'confidence' => 0.0];
        }

        return ['category' => $t, 'confidence' => 0.75];
    }
}
