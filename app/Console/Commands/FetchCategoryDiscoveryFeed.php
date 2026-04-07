<?php

namespace App\Console\Commands;

use App\Helpers\CategoryFeedBrandScores;
use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Helpers\VidgazeCategoryFeedCache;
use App\Http\Controllers\Tools\NanoController;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCategoryDiscoveryFeed extends Command
{
    protected $signature = 'app:fetch-category-discovery-feed';

    protected $description = 'Generate AI search queries per featured category, run unified search, cache snippet + videos (with brand scores)';

    public function handle(): int
    {
        if (empty(trim((string) config('services.nanogpt.key')))) {
            $this->warn('NANOGPT_API_KEY not set; skipping category discovery feed.');

            return self::SUCCESS;
        }

        $slugs = config('vidgaze.category_discovery.slugs', []);
        if (! is_array($slugs) || $slugs === []) {
            $this->warn('VIDGAZE_CATEGORY_DISCOVERY_SLUGS empty; nothing to do.');

            return self::SUCCESS;
        }

        $maxVideos = (int) config('vidgaze.category_discovery.max_videos_per_category', 24);
        $model = (string) config('services.nanogpt.search_ranking_model', 'gemini-2.5-flash-lite');

        $manifest = VidgazeCategoryFeedCache::getManifest();
        $bySlug = [];
        foreach ($manifest['categories'] ?? [] as $row) {
            if (! empty($row['slug'])) {
                $bySlug[strtolower((string) $row['slug'])] = VidgazeCategoryFeedCache::normalizeEntry($row);
            }
        }

        $updatedThisRun = [];

        foreach ($slugs as $slug) {
            $slug = strtolower(trim((string) $slug));
            if ($slug === '') {
                continue;
            }

            $category = Category::query()->where('slug', $slug)->first();
            if ($category === null) {
                $this->warn("No category with slug [{$slug}] — skip.");

                continue;
            }

            CategoryFeedBrandScores::applyDecay((int) $category->id);

            $query = $this->generateSearchQuery($slug, (string) $category->name, $category->tags_json, $model);
            if ($query === null || $query === '') {
                Log::warning('Category discovery: empty query', ['slug' => $slug]);

                continue;
            }

            try {
                $dto = new SearchQueryDTO($query, 10);
                Search::searchJobs($dto);
                $ready = Search::waitForSearchCache($dto, 300);
                if (! $ready) {
                    Log::warning('Category discovery: search cache timeout', ['slug' => $slug, 'query' => $query]);

                    continue;
                }
                $saved = Search::searchResults($dto, true);
                $videos = $saved['videos'] ?? [];
                $ids = [];
                foreach ($videos as $video) {
                    if (is_object($video) && isset($video->id)) {
                        $ids[] = (int) $video->id;
                    }
                    if (count($ids) >= $maxVideos) {
                        break;
                    }
                }
                if ($ids === []) {
                    $this->warn("No videos for category [{$slug}] query [{$query}]");

                    continue;
                }

                $updatedThisRun[$slug] = [
                    'category_id' => (int) $category->id,
                    'slug' => $slug,
                    'name' => (string) $category->name,
                    'label' => $query,
                    'video_ids' => $ids,
                ];

                CategoryFeedBrandScores::syncScoresForCategory((int) $category->id, $ids);
                $this->info("Category [{$slug}]: {$query} — ".count($ids).' video(s).');
            } catch (\Throwable $e) {
                Log::error('Category discovery failed', ['slug' => $slug, 'message' => $e->getMessage()]);
            }
        }

        foreach ($slugs as $slug) {
            $slug = strtolower(trim((string) $slug));
            if ($slug === '' || isset($updatedThisRun[$slug])) {
                continue;
            }
            if (isset($bySlug[$slug])) {
                $updatedThisRun[$slug] = $bySlug[$slug];
            }
        }

        $ordered = [];
        foreach ($slugs as $slug) {
            $slug = strtolower(trim((string) $slug));
            if ($slug === '') {
                continue;
            }
            if (isset($updatedThisRun[$slug])) {
                $ordered[] = $updatedThisRun[$slug];
            }
        }

        if ($ordered !== []) {
            VidgazeCategoryFeedCache::replaceManifest($ordered);
            $this->info('Saved category discovery manifest ('.count($ordered).' slot(s)).');
        } else {
            $this->info('Category discovery manifest unchanged (no successful updates).');
        }

        return self::SUCCESS;
    }

    private function generateSearchQuery(string $slug, string $name, mixed $tagsJson, string $model): ?string
    {
        $tags = '';
        if (is_array($tagsJson)) {
            $tags = json_encode($tagsJson);
        } elseif (is_string($tagsJson)) {
            $tags = $tagsJson;
        }

        $date = now()->format('Y-m-d');
        $nano = new NanoController;
        $prompt = "You choose ONE short search query for a general-purpose video site.\n"
            ."Category slug: {$slug}\n"
            ."Category name: {$name}\n"
            ."Tags JSON (may be empty): {$tags}\n"
            ."Today: {$date}\n\n"
            .'Return ONLY the search query string: a few words, safe for all audiences, no hashtags, '
            .'no quotes, no explanation. Prefer timely or evergreen topics that fit this category.';

        try {
            $response = $nano->getChatCompletion([
                ['role' => 'system', 'content' => 'Reply with the search query line only. No markdown, no JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ], $model, ['max_tokens' => 80, 'temperature' => 0.9], true);

            $line = trim((string) ($response['choices'][0]['message']['content'] ?? ''));
            $line = preg_replace('/^["\']|["\']$/', '', $line);
            $line = trim((string) preg_replace('/\s+/', ' ', $line));

            if ($line !== '' && strlen($line) <= 160) {
                return $line;
            }
        } catch (\Throwable $e) {
            Log::warning('Category discovery AI query failed', ['slug' => $slug, 'message' => $e->getMessage()]);
        }

        return null;
    }
}
