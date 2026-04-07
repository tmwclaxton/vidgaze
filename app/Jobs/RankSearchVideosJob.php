<?php

namespace App\Jobs;

use App\Helpers\SearchVideoAiRanker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RankSearchVideosJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $uniqueFor = 120;

    public int $timeout = 120;

    public int $tries = 2;

    /**
     * @param  list<int>  $sortedVideoIds
     * @param  list<array{id:int, title:string, preferred_source:string}>  $videoStubs
     */
    public function __construct(
        public string $searchQuery,
        public array $sortedVideoIds,
        public array $videoStubs,
        public string $model,
    ) {}

    public function uniqueId(): string
    {
        $needle = mb_strtolower(trim($this->searchQuery)).'|'.implode(',', $this->sortedVideoIds);

        return 'srank:'.sha1($needle);
    }

    public function handle(): void
    {
        SearchVideoAiRanker::computeAndCacheRankFromStubs(
            $this->searchQuery,
            $this->sortedVideoIds,
            $this->videoStubs,
            $this->model
        );
    }
}
