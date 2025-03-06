<?php

namespace App\Console\Commands;

use App\Helpers\ResultDTO;
use Illuminate\Console\Command;

class getVimeoFeaturedVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-vimeo-featured-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the featured videos from Vimeo and save them to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $platform = new \App\Helpers\PlatformAPIs\Vimeo();
        $results = $platform->getFeaturedVideos();

        $savedResults = ResultDTO::saveAll($results);

        // iterate through and changed pinned to true and pin_expires_at to 1 week from now
        foreach ($savedResults as $result) {
            $result->pinned = true;
            $result->pin_expires_at = now()->addWeek();
            $result->save();
        }
    }
}
