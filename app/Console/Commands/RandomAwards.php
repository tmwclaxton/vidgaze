<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RandomAwards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:random-awards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give random awards to videos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $job = new \App\Jobs\RandomAwards();
        $job->handle();
    }
}
