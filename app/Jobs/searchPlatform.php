<?php

namespace App\Jobs;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\iSearchable;
use App\Helpers\SearchQueryDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class searchPlatform implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public SearchQueryDTO $searchQuery,
        public iSearchable $platformAPI
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $results = $this->platformAPI::search($this->searchQuery);
        Redis::client()->set(
            "search:". $this->platformAPI::getPlatform()->getPrefix().":".
            $this->searchQuery->query
            ,
            json_encode($results)
        );
    }
}
