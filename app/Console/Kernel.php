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
        // $schedule->command('inspire')->hourly();
        
        // Run the JSON integrity check daily
        $schedule->command('app:check-json-integrity')->daily();
        
        // Run the database JSON repair weekly
        $schedule->command('app:repair-database-json')->weekly();
        
        // Generate statistics hourly
        $schedule->command('app:generate-statistics')->hourly();
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