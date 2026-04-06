<?php

namespace App\Console\Commands;

use App\Helpers\ApifyTwitterTrends;
use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use App\Helpers\VidgazeTrendFeedCache;
use App\Helpers\VidgazeTrendPickCache;
use App\Http\Controllers\Tools\NanoController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FetchTwitterTrendsSearch extends Command
{
    protected $signature = 'app:fetch-twitter-trends-search';

    protected $description = 'Fetch Twitter trends via Apify, run unified search per trend, merge fresh video IDs into VidGaze trend picks cache';

    public function handle(): int
    {
        if (empty(trim((string) config('services.apify.token')))) {
            $this->warn('APIFY_TOKEN not set; skipping Twitter trends search.');

            return self::SUCCESS;
        }

        $maxTrends = (int) config('vidgaze.trend_picks.max_trends_per_run', 8);
        $videosEach = (int) config('vidgaze.trend_picks.max_videos_per_trend', 3);
        $videosPerTrendFeed = (int) config('vidgaze.trend_picks.max_videos_per_trend_feed', 24);
        $useAiFilter = (bool) config('vidgaze.trend_picks.ai_filter_trends', true);

        $rawTrends = ApifyTwitterTrends::fetchTrendStrings();
        if ($rawTrends === []) {
            $this->warn('No trends returned from Apify.');

            return self::SUCCESS;
        }

        $trends = $useAiFilter ? $this->filterTrendsWithAi($rawTrends, $maxTrends) : array_slice($rawTrends, 0, $maxTrends);
        $trends = array_slice($trends, 0, $maxTrends);

        if ($trends === []) {
            $this->warn('No trends to search after filtering.');

            return self::SUCCESS;
        }

        $this->info('Processing '.count($trends).' trend(s).');

        $allVideoIds = [];
        $manifestTopics = [];
        $datePrefix = now()->format('Y-m-d');

        foreach ($trends as $trend) {
            $dedupeKey = 'vidgaze:trend_search_day:'.md5(strtolower($trend)).':'.$datePrefix;
            if (Redis::get($dedupeKey)) {
                continue;
            }

            try {
                $dto = new SearchQueryDTO($trend, 10);
                Search::searchJobs($dto);
                $ready = Search::waitForSearchCache($dto, 300);
                if (! $ready) {
                    Log::warning('Twitter trend search: cache timeout', ['trend' => $trend]);

                    continue;
                }
                $saved = Search::searchResults($dto, true);
                $videos = $saved['videos'] ?? [];
                $idsForFeed = [];
                $takenPicks = 0;
                foreach ($videos as $video) {
                    if (! is_object($video) || ! isset($video->id)) {
                        continue;
                    }
                    $vid = (int) $video->id;
                    if (count($idsForFeed) < $videosPerTrendFeed) {
                        $idsForFeed[] = $vid;
                    }
                    if ($takenPicks < $videosEach) {
                        $allVideoIds[] = $vid;
                        $takenPicks++;
                    }
                    if (count($idsForFeed) >= $videosPerTrendFeed && $takenPicks >= $videosEach) {
                        break;
                    }
                }
                if ($idsForFeed !== []) {
                    $manifestTopics[] = [
                        'key' => VidgazeTrendFeedCache::trendKey($trend),
                        'label' => $trend,
                        'video_ids' => $idsForFeed,
                    ];
                }
                Redis::setex($dedupeKey, 86400, '1');
            } catch (\Throwable $e) {
                Log::error('Twitter trend search failed', ['trend' => $trend, 'message' => $e->getMessage()]);
            }
        }

        if ($manifestTopics !== []) {
            $existing = VidgazeTrendFeedCache::getManifest()['topics'] ?? [];
            $byKey = [];
            foreach ($existing as $row) {
                if (! empty($row['key'])) {
                    $byKey[$row['key']] = $row;
                }
            }
            foreach ($manifestTopics as $row) {
                $byKey[$row['key']] = $row;
            }
            $ordered = [];
            foreach ($existing as $row) {
                $k = $row['key'] ?? '';
                if ($k !== '' && isset($byKey[$k])) {
                    $ordered[] = $byKey[$k];
                    unset($byKey[$k]);
                }
            }
            $ordered = array_merge($ordered, array_values($byKey));
            VidgazeTrendFeedCache::replaceManifest($ordered);
            $this->info('Updated home trend feed ('.count($manifestTopics).' topic(s) in this run, '.count($ordered).' total).');
        }

        if ($allVideoIds !== []) {
            VidgazeTrendPickCache::mergePush($allVideoIds);
            $this->info('Merged '.count(array_unique($allVideoIds)).' video id(s) into trend picks cache.');
        } elseif ($manifestTopics === []) {
            $this->info('No new videos collected from trends this run.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $rawTrends
     * @return list<string>
     */
    private function filterTrendsWithAi(array $rawTrends, int $maxTrends): array
    {
        if (empty(trim((string) config('services.nanogpt.key')))) {
            return array_slice($rawTrends, 0, $maxTrends);
        }

        $nano = new NanoController;
        $payload = json_encode(array_values($rawTrends));
        $prompt = "You filter Twitter/X trend strings for a general-purpose video search site.\n"
            ."Given this JSON array of trend strings, return ONLY a JSON array of at most {$maxTrends} strings "
            .'that are safe, neutral search queries (no NSFW, slurs, hate, doxxing, or purely personal names). '
            ."Prefer topics that will yield news/commentary/entertainment videos. Strip # and keep phrases short.\n"
            ."Trends JSON:\n{$payload}";

        try {
            $response = $nano->getChatCompletion([
                ['role' => 'system', 'content' => 'Return only valid JSON: a JSON array of strings, no markdown.'],
                ['role' => 'user', 'content' => $prompt],
            ], (string) config('services.nanogpt.search_ranking_model', 'gemini-2.0-flash-lite'), ['max_tokens' => 500], false);
            $content = trim($response['choices'][0]['message']['content'] ?? '');
            $content = preg_replace('/^```json\s*|\s*```$/i', '', $content);
            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                return array_slice($rawTrends, 0, $maxTrends);
            }
            $out = [];
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $t = trim($item);
                    if ($t !== '' && strlen($t) <= 120) {
                        $out[] = $t;
                    }
                }
            }

            return array_slice(array_values(array_unique($out)), 0, $maxTrends);
        } catch (\Throwable $e) {
            Log::warning('Trend AI filter failed, using raw slice', ['message' => $e->getMessage()]);

            return array_slice($rawTrends, 0, $maxTrends);
        }
    }
}
