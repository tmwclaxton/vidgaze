<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RandomViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:random-views';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give random views to videos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $job = new \App\Jobs\RandomViews();
        $job->handle();
    }
}
