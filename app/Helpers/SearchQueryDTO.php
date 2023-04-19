<?php

namespace App\Helpers;

use App\Enums\Platforms;

class SearchQueryDTO
{
    public string $query;
    public int $maxResults; //per api search

    public $platform;
    private array $platforms;
    public array $kinds;

    public function __construct(string $query, int $maxResults = 5){
        $this->query = $query;
        $this->maxResults = $maxResults;
    }

    public static function getRedisPlatformSearchKey($platform, $query){
        return "search_$platform:".$query;
    }

    public static function getRedisPlatformSearchTimeKey($platform, $query){
        return "search_time_$platform:".$query;
    }

    /**
     * returns the redis key for checking if a search batch has completed
     * @param $query
     * @return string
     */
    public static function getRedisBatchKey($query): string{
        return "search_batch_completed:".$query;
    }

    /**
     * returns the redis key for checking if a search batch has completed
     * @param $query
     * @return string
     */
    public static function getRedisBatchTimeKey($query): string{
        return "search_batch_time:".$query;
    }

    /**
     * returns $platforms if set, else it gets all the VidGaze supported platforms
     * @return array
     */
    public function getPlatforms(): array
    {
        return $this->platforms ?? Platforms::getSupportedPlatforms();
    }
}
