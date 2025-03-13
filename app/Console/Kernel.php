<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        // this gets stream from 1 twitch category every minute
        //$schedule->command('refresh:one_twitch_category')->everyMinute();
        // this refreshes the twitch category info every 6 hours
        //$schedule->command('refresh:twitch-category-info')->everySixHours();
        //$schedule->command('refresh:streams')->everyMinute();

        // cmds: refresh:top-categories, refresh:twitch-category-info, refresh:streams

        $schedule->command('delete:old_live_viewers')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('refresh:subscriptions')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('refresh:top-categories')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('refresh:twitch-category-info')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('refresh:streams')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('app:delete-old-streams')->everyThirtyMinutes()->withoutOverlapping();
        $schedule->command('app:get-vimeo-featured-videos')->daily()->withoutOverlapping();
        $schedule->command('app:get-rumble-featured-videos')->daily()->withoutOverlapping();
        $schedule->command('app:delete-old-pins')->daily()->withoutOverlapping();
        $schedule->command('app:categorise-videos')->everyTwoMinutes()->withoutOverlapping();
        $schedule->command('app:get-rumble-banners')->everyTwoHours()->withoutOverlapping();
        $schedule->command('app:search-videos-for-categories')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
