<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [

        'App\Console\Commands\RunPayroll',
        'App\Console\Commands\TimesheetToInvoice'
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('verify:invoice')->everySecond();
        $schedule->command('run:payroll')->everySecond();
        $schedule->command('app:timesheet-to-invoice')->everySecond();

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
