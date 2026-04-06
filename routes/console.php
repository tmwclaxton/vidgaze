<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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

Schedule::command('delete:old_live_viewers')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('refresh:subscriptions')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('refresh:top-categories')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('refresh:twitch-category-info')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('refresh:streams')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('app:delete-old-streams')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('app:get-vimeo-featured-videos')->daily()->withoutOverlapping();
Schedule::command('app:get-rumble-featured-videos')->daily()->withoutOverlapping();
Schedule::command('app:delete-old-pins')->daily()->withoutOverlapping();
Schedule::command('app:categorise-videos')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('app:get-rumble-banners')->everyTwoHours()->withoutOverlapping();
Schedule::command('app:search-videos-for-categories')->hourly()->withoutOverlapping();

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
