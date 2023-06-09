<?php

namespace App\Helpers;

use App\Enums\Platform;
use App\Jobs\searchPlatform;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;

class Search
{

    public static function getRedisSearchKey(Platform $platform, SearchQueryDTO $query): string
    {
        return "search:" . $platform->getPrefix() . ":" . $query->query;
    }

    public static function search(SearchQueryDTO $searchQuery, int $max_wait = 5)
    {
        $max_wait = $max_wait >=0 ? $max_wait : 5;

        // check the Redis cache for each platform in query
        $cache_results = [];
        $platforms_to_search = [];
        foreach ($searchQuery->getPlatforms() as $platform) {
            $result = Redis::client()->get(self::getRedisSearchKey($platform, $searchQuery));
            if ($result) {
                $cache_results[$platform->value] = json_decode($result);
            }
            else {
                $platforms_to_search[] = $platform;
            }
        }

        // if all platforms are in the cache, return the results
        if (count($platforms_to_search) === 0) {
            return $cache_results;
        }

        // add a job for each platform that is not in the cache
        $search_jobs =[];
        foreach ($platforms_to_search as $platform) {
            $search_jobs[] = new searchPlatform($searchQuery, $platform);
        }

        // dispatch batch
        $batch = Bus::batch($search_jobs)
            ->finally(function (Batch $batch) use ($platforms_to_search, $searchQuery) {
                self::getRedisCacheResults($searchQuery, $platforms_to_search);
            })->onQueue('search')->onConnection('redis')->dispatch();

        // wait to finish or wait max seconds
        $time = 0;
        while (!Bus::findBatch($batch->id)->finished() && $time < $max_wait) {
            usleep(500000);
            $time+=0.5;
        }

        // return results in Redis cache
        $cache_results[] = self::getRedisCacheResults($searchQuery, $platforms_to_search);
        return $cache_results;
    }

    public static function getRedisExpire(): int
    {
        // 1 day
        return 86400;
    }

    private static function getRedisCacheResults(SearchQueryDTO $searchQuery, array $platforms): array
    {
        $results = [];
        foreach ($platforms as $platform) {
            $result = Redis::client()->get(self::getRedisSearchKey($platform, $searchQuery));
            if ($result) {
                $results[$platform->value] = json_decode($result);
            }
        }
        return $results;
    }
}
