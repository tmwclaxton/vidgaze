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
        // this prunes the telescope database every 48 hours
        $schedule->command('telescope:prune --hours=48')->daily();

        // prune sanctum tokens every 24 hours
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // this gets stream from 1 twitch category every minute
        //$schedule->command('refresh:one_twitch_category')->everyMinute();
        // this refreshes the twitch category info every 6 hours
        //$schedule->command('refresh:twitch-category-info')->everySixHours();
        //$schedule->command('refresh:streams')->everyMinute();
        $schedule->command('delete:old_live_viewers')->everyMinute();
        //$schedule->command('refresh:subscriptions')->everyMinute();
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
