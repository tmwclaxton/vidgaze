<?php

namespace App\Console\Commands;

use App\Http\Controllers\Tools\NanoController;
use App\Models\Category;
use App\Models\CreatorModels\Creator;
use Illuminate\Console\Command;

class CategoriseCreators extends Command
{
    protected $signature = 'app:categorise-creators';

    protected $description = 'Assign a category to creators using AI from recent video titles and descriptions (batch per channel)';

    public function handle(): int
    {
        $categories = Category::all();
        $categoryNames = array_values(array_diff($categories->pluck('name')->toArray(), ['VidGaze Picks']));
        if ($categoryNames === []) {
            return self::SUCCESS;
        }

        $minConfidence = (float) config('vidgaze.categorisation_min_confidence', 0.55);
        $nano = new NanoController;

        $creators = Creator::query()
            ->whereNull('category_id')
            ->whereHas('videos')
            ->inRandomOrder()
            ->take(12)
            ->get();

        foreach ($creators as $creator) {
            try {
                $samples = $creator->videos()
                    ->orderByDesc('time_published')
                    ->take(5)
                    ->get(['title', 'description']);

                if ($samples->isEmpty()) {
                    continue;
                }

                $lines = [];
                foreach ($samples as $v) {
                    $lines[] = '- '.$v->title.': '.str($v->description ?? '')->limit(200);
                }
                $blob = implode("\n", $lines);

                $prompt = "Channel videos sample:\n{$blob}\n\n"
                    .'Available categories (JSON array): '.json_encode($categoryNames)."\n\n"
                    .'Return ONLY JSON: {"category":"<exact category name or null>","confidence":0.0} '
                    .'Use null if nothing fits well.';

                $response = $nano->getChatCompletion([
                    ['role' => 'system', 'content' => 'You assign one editorial category per channel from the allowed list. JSON only, no markdown.'],
                    ['role' => 'user', 'content' => $prompt],
                ], (string) config('services.nanogpt.search_ranking_model', 'gemini-2.5-flash-lite'), ['max_tokens' => 120], false);

                $content = trim($response['choices'][0]['message']['content'] ?? '');
                $parsed = $this->parseJsonCategory($content);

                $categoryName = $parsed['category'] ?? null;
                $confidence = isset($parsed['confidence']) ? (float) $parsed['confidence'] : 0.0;

                if ($categoryName === null || ! in_array($categoryName, $categoryNames, true) || $confidence < $minConfidence) {
                    continue;
                }

                $category = $categories->firstWhere('name', $categoryName);
                if ($category) {
                    $creator->category_id = $category->id;
                    $creator->save();
                }
            } catch (\Throwable $e) {
                logger()->error('CategoriseCreators failed for creator '.$creator->id.': '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array{category: ?string, confidence: float}
     */
    private function parseJsonCategory(string $raw): array
    {
        $t = preg_replace('/^```json\s*|\s*```$/i', '', trim($raw));
        $decoded = json_decode($t, true);
        if (is_array($decoded)) {
            $cat = $decoded['category'] ?? null;
            if (is_string($cat) && strtolower($cat) === 'null') {
                $cat = null;
            }

            return [
                'category' => is_string($cat) ? $cat : null,
                'confidence' => isset($decoded['confidence']) ? (float) $decoded['confidence'] : 0.0,
            ];
        }

        return ['category' => null, 'confidence' => 0.0];
    }
}
