<?php

namespace App\Console\Commands;

use App\Models\StreamModels\Stream;
use Illuminate\Console\Command;

class deleteOldStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-streams';

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
        $streams = Stream::where('updated_at', '<', now()->subHour(5))->get();
        $streams->each->delete();
    }
}
