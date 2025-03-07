<?php

namespace App\Console\Commands;

use App\Models\VideoModels\Video;
use Illuminate\Console\Command;

class deleteOldPins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-pins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Any videos with a pin_expires_at date in the past will have their pinned status removed and pin_expires_at set to null';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $affected = Video::where('pin_expires_at', '<', now())->update([
            'pinned' => false,
            'pin_expires_at' => null,
        ]);

        $this->info("Updated $affected videos");
    }
}
