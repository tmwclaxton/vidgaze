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

class searchPlatform implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        Redis::client()->set(
            Search::getRedisSearchKey($this->platform->getPlatformClass()::getPlatform(), $this->searchQuery),
            json_encode($results),
            Search::getRedisExpire()
        );
    }
}
