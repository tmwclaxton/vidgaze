<?php

namespace App\Helpers;

use App\Enums\Platform;
use App\Jobs\searchPlatform;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use function PHPUnit\Framework\isInstanceOf;

class Search
{

    public static function getRedisSearchKey(Platform $platform, SearchQueryDTO $query): string
    {
        return "search:" . $platform->getPrefix() . ":" . $query->query;
    }

    public static function search(SearchQueryDTO $searchQuery, bool $saveAndReturnModels = true, int $max_wait = 5): array
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
        if (count($platforms_to_search) != 0) {
            // add a job for each platform that is not in the cache
            $search_jobs =[];
            foreach ($platforms_to_search as $platform) {
                $search_jobs[] = new searchPlatform($searchQuery, $platform);
            }

            // dispatch batch
            $batch = Bus::batch($search_jobs)
               /* ->finally(function (Batch $batch) use ($platforms_to_search, $searchQuery) {
                    self::getRedisCacheResults($searchQuery, $platforms_to_search);
                })*/->onQueue('search')->onConnection('redis')->dispatch();

            // wait to finish or wait max seconds
            $time = 0;
            while (!Bus::findBatch($batch->id)->finished() && $time < $max_wait) {
                usleep(500000);
                $time+=0.5;
            }

            // return results in Redis cache
            $cache_results = array_merge($cache_results, self::getRedisCacheResults($searchQuery, $platforms_to_search));
        }

        $results = [];
        foreach ($cache_results as $result) {
            $results = array_merge($results, $result);
        }
        $results = ResultDTO::convertArray($results);

        if($saveAndReturnModels) {
            $sorted_results = [];
            foreach (ResultDTO::saveAll($results) as $result) {
                match (get_class($result)) {
                    'App\Models\CreatorModels\Creator' => $sorted_results['creators'][] = $result,
                    'App\Models\VideoModels\Video' => $sorted_results['videos'][] = $result,
                    'App\Models\StreamModels\Stream' => $sorted_results['streams'][] = $result,
                };
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
