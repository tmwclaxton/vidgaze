<?php

namespace App\Console\Commands;

use Google\Service\Storagetransfer\ErrorLogEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Octane\Swoole\SwooleClient;

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
        // if laravel.log and swoole_http.log aren't present or empty, don't backup
        if (!in_array('laravel.log', $localFiles) || !in_array('swoole_http.log', $localFiles) ) {
            $this->info('No files to backup');
            return;
        }

        $this->info('Backing up ' . count($localFiles) . ' files');
        $cloudDisk = Storage::disk('s3');
        $pathPrefix = 'vidgaze_production_logs' . DIRECTORY_SEPARATOR . Carbon::now() . DIRECTORY_SEPARATOR;

        foreach ($localFiles as $file) {
            $this->info('Backing up ' . $file);
            $contents = $localDisk->get($file);
            $cloudLocation = $pathPrefix . $file;
            $cloudDisk->put($cloudLocation, $contents);
        }

        // wipe laravel.log and swoole_http.log and worker.log
        $localDisk->delete('laravel.log');
        $localDisk->delete('swoole_http.log');
        $localDisk->delete('worker.log');

        // create new laravel.log and swoole_http.log
        $localDisk->put('laravel.log', '');
        $localDisk->put('swoole_http.log', '');
        $localDisk->put('worker.log', '');

        // add a log entry to laravel.log
        Log::debug('Previous logs backed up to s3');

        $this->info('BackupLogs command executed');
    }

}
