<?php

namespace App\Console\Commands;

use App\Helpers\PlatformAPIs\Rumble;
use App\Helpers\ResultDTO;
use Illuminate\Console\Command;

class getRumbleFeaturedVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-rumble-featured-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $platform = new \App\Helpers\PlatformAPIs\Rumble();
        $searchQuery = new \App\Helpers\SearchQueryDTO('news', 10, [$platform->getPlatform()]);
        $results = $platform->search($searchQuery);

        $savedResults = ResultDTO::saveAll($results);

        // iterate through and changed pinned to true and pin_expires_at to 1 week from now
        foreach ($savedResults as $result) {
            $result->pinned = true;
            $result->pin_expires_at = now()->addDay();
            $result->save();
        }
    }
}
