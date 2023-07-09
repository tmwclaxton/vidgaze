<?php

namespace App\Console\Commands;

use App\Helpers\PlatformAPIs\Twitch;
use Illuminate\Console\Command;

class RefreshStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:streams';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update YouTube and Twitch';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Twitch::updateStreamerStatus();
        return Command::SUCCESS;
    }
}
