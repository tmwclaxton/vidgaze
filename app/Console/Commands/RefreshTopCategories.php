<?php

namespace App\Console\Commands;

use App\Helpers\PlatformAPIs\Twitch;
use App\Models\Category;
use Illuminate\Console\Command;

class RefreshTopCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:top-categories';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Top Streaming Categories on YouTube and Twitch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!\App\Helpers\PlatformAPIs\PlatformCredentials::ensure(\App\Enums\Platform::Twitch, $this->getName())) {
            return 0;
        }

        Twitch::updateTopCategories(8, 8);
        return Command::SUCCESS;
    }
}
