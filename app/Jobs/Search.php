<?php

namespace App\Jobs;

use App\Helpers\PlatformAPIs\YouTube;
use App\Helpers\Search as Api_Search;
use App\Helpers\SearchQueryDTO;
use App\Helpers\SearchResultDTO;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class Search implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * @var SearchQueryDTO
     */
    public $searchQuery;
    public $platform;

    public $tries = 1;
    public $timeout = 20;
    public $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SearchQueryDTO $searchQuery, $platform = null)
    {
        $this->searchQuery = $searchQuery;
        $this->platform = $platform ?? $searchQuery->platform;
        $this->onQueue('search');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //return Redis::client()->lPush("search1", "fired");
        $key = SearchQueryDTO::getRedisPlatformSearchKey($this->platform, $this->searchQuery->query);
        $timeKey = SearchQueryDTO::getRedisPlatformSearchKey($this->platform, $this->searchQuery->query);

        $timeKey = "search_time_$this->platform:".$this->searchQuery->query;



        $start = microtime(true);

        // If cached return cache
        if(Redis::client()->exists($key)){
            return Redis::client()->get($key);
        }

        // get api results
        $results = Api_Search::platformSearch($this->searchQuery, $this->platform)['results'];

        // save to database
        SearchResultDTO::convertResultDTOToModels($results);

        // cache results
        Redis::client()->set($key, json_encode($results));

        // set time info
        Redis::client()->set($timeKey, microtime(true) - $start);
    }

    public function uniqueId()
    {
        return $this->searchQuery->query. $this->platform;
    }
}
