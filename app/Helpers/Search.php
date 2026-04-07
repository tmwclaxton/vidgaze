<?php

namespace App\Helpers;

use App\Enums\Platform;
use App\Jobs\SearchPlatform;
use App\Models\PodcastModels\Podcast;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;

class Search
{
    public static function getRedisSearchKey(Platform $platform, SearchQueryDTO $query): string
    {
        return 'search:'.$platform->getPrefix().':'.$query->query;
    }

    public static function searchJobs(SearchQueryDTO $searchQuery)
    {
        $cache_results = self::fetchDecodedCacheByPlatform($searchQuery);
        $platforms_to_search = [];
        foreach ($searchQuery->getPlatforms() as $platform) {
            if (! isset($cache_results[$platform->value])) {
                $platforms_to_search[] = $platform;
            }
        }

        if (count($platforms_to_search) != 0) {
            $search_jobs = [];
            foreach ($platforms_to_search as $platform) {
                $search_jobs[] = new SearchPlatform($searchQuery, $platform);
            }

            try {
                Bus::batch($search_jobs)->onQueue('search')->onConnection('redis')->dispatch();
            } catch (\Throwable $th) {
                return [];
            }
        }
    }

    /**
     * Poll Redis until every platform has cached search JSON or timeout.
     */
    public static function waitForSearchCache(SearchQueryDTO $searchQuery, int $timeoutSeconds = 300, int $pollMilliseconds = 400): bool
    {
        $platforms = $searchQuery->getPlatforms();
        if ($platforms === []) {
            return true;
        }

        $keys = [];
        foreach ($platforms as $platform) {
            $keys[] = self::getRedisSearchKey($platform, $searchQuery);
        }

        $deadline = microtime(true) + $timeoutSeconds;
        while (microtime(true) < $deadline) {
            $values = Redis::connection()->mget($keys);
            $allPresent = true;
            foreach ($values as $val) {
                if (! is_string($val) || $val === '') {
                    $allPresent = false;
                    break;
                }
            }
            if ($allPresent) {
                return true;
            }
            usleep(max(1, $pollMilliseconds) * 1000);
        }

        return false;
    }

    public static function searchResults(SearchQueryDTO $searchQuery, bool $saveAndReturnModels = true): array
    {
        $cache_results = self::fetchDecodedCacheByPlatform($searchQuery);

        $results = [];
        foreach ($cache_results as $result) {
            $results = array_merge($results, $result);
        }
        $results = ResultDTO::convertArray($results);

        if ($saveAndReturnModels) {
            $sorted_results = [
                'creators' => [],
                'videos' => [],
                'streams' => [],
                'podcasts' => [],
            ];
            foreach (ResultDTO::saveAll($results) as $result) {
                match (get_class($result)) {
                    'App\Models\CreatorModels\Creator' => $sorted_results['creators'][] = $result,
                    'App\Models\VideoModels\Video' => $sorted_results['videos'][] = $result,
                    'App\Models\StreamModels\Stream' => $sorted_results['streams'][] = $result,
                    Podcast::class => $sorted_results['podcasts'][] = $result,
                    default => null,
                };
            }
            foreach ($sorted_results['podcasts'] ?? [] as $podcast) {
                if ($podcast instanceof Podcast) {
                    $podcast->loadMissing('creator');
                }
            }

            return $sorted_results;
        }

        $sorted_results = [];
        foreach ($results as $result) {
            $sorted_results[$result->kind->value][] = $result;
        }

        return $sorted_results;
    }

    public static function getRedisExpire(): int
    {
        // 1 day
        return 86400;
    }

    /**
     * @return array<string, mixed> Platform enum value => decoded JSON (per platform), only cache hits
     */
    private static function fetchDecodedCacheByPlatform(SearchQueryDTO $searchQuery): array
    {
        $platforms = $searchQuery->getPlatforms();
        if ($platforms === []) {
            return [];
        }

        $keys = [];
        $platformValues = [];
        foreach ($platforms as $platform) {
            $keys[] = self::getRedisSearchKey($platform, $searchQuery);
            $platformValues[] = $platform->value;
        }

        $values = Redis::connection()->mget($keys);

        $out = [];
        foreach ($platformValues as $i => $platformValue) {
            $val = $values[$i] ?? null;
            if (! is_string($val) || $val === '') {
                continue;
            }
            $decoded = json_decode($val);
            if ($decoded !== null) {
                $out[$platformValue] = $decoded;
            }
        }

        return $out;
    }
}
