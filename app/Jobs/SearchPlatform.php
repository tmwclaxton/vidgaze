<?php

namespace App\Jobs;

use App\Enums\Platform;
use App\Helpers\Search;
use App\Helpers\SearchQueryDTO;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class SearchPlatform implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Seconds before the worker kills the job (Apify YouTube run-sync can run for minutes).
     */
    public int $timeout = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SearchQueryDTO $searchQuery,
        public Platform $platform
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $results = $this->platform->getPlatformClass()::search($this->searchQuery);
        Redis::setex(
            Search::getRedisSearchKey($this->platform->getPlatformClass()::getPlatform(), $this->searchQuery),
            Search::getRedisExpire(),
            json_encode($results)
        );
    }
}
