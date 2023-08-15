<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BackupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup logs to s3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $localDisk = Storage::disk('logs');
        $localFiles = $localDisk->allFiles();
        $this->info('Backing up ' . count($localFiles) . ' files');
        $cloudDisk = Storage::disk('s3');
        $pathPrefix = 'vidgaze_production_logs' . DIRECTORY_SEPARATOR . Carbon::now() . DIRECTORY_SEPARATOR;
        foreach ($localFiles as $file) {
            $this->info('Backing up ' . $file);
            $contents = $localDisk->get($file);
            $cloudLocation = $pathPrefix . $file;
            $cloudDisk->put($cloudLocation, $contents);
        }
        $this->info('BackupLogs command executed');
    }

}
