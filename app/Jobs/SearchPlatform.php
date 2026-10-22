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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SearchPlatform implements ShouldQueue
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
        try {
            $results = $this->platform->getPlatformClass()::search($this->searchQuery);
        } catch (\Throwable $e) {
            // Store an empty result so the search page knows this platform is
            // done instead of polling for it until it gives up.
            Log::warning("Search on {$this->platform->name} failed: " . $e->getMessage());
            $results = [];
        }
        Redis::client()->set(
            Search::getRedisSearchKey($this->platform, $this->searchQuery),
            json_encode($results),
            Search::getRedisExpire()
        );
    }
}
