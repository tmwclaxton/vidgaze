<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterate a queue of creators who need their videos and streams refreshed
     because they are on someone\'s subscription list who has been online recently ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
