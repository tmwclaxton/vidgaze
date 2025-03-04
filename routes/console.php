<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('app:random-views')
//     ->description('Give random views to videos')
//     ->everyMinute()->withoutOverlapping(1);
//
// Schedule::command('app:random-awards')
//     ->description('Give random awards to videos')
//     ->everyMinute()->withoutOverlapping(1);

Artisan::command('app:clear-logs', function () {
    $this->comment('Clearing logs...');
    exec('echo "" > storage/logs/laravel.log');
    $this->comment('Logs cleared');
})->purpose('Clear logs');
