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
use Throwable;

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
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $platformKey = $this->platform->value;
        try {
            $results = $this->platform->getPlatformClass()::search($this->searchQuery);
            if (! is_array($results)) {
                Log::warning('SearchPlatform: search() did not return an array', ['platform' => $platformKey]);
                $results = [];
            }
        } catch (Throwable $e) {
            Log::error('SearchPlatform: search failed', [
                'platform' => $platformKey,
                'message' => $e->getMessage(),
            ]);
            report($e);
            $results = [];
        }

        $encoded = json_encode($results);
        if ($encoded === false) {
            Log::error('SearchPlatform: json_encode failed', ['platform' => $platformKey]);
            $encoded = '[]';
        }

        Redis::setex(
            Search::getRedisSearchKey($this->platform->getPlatformClass()::getPlatform(), $this->searchQuery),
            Search::getRedisExpire(),
            $encoded
        );
    }
}
