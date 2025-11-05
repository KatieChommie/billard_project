<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CancelExpiredOrders;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CancelExpiredOrders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $minutes = config('orders.scheduler_interval_minutes', 10);

        // Map common intervals to expressive scheduler methods when possible
        if ($minutes === 1) {
            $schedule->command('app:cancel-expired-orders')->everyMinute();
        } elseif ($minutes === 5) {
            $schedule->command('app:cancel-expired-orders')->everyFiveMinutes();
        } elseif ($minutes === 10) {
            $schedule->command('app:cancel-expired-orders')->everyTenMinutes();
        } else {
            // Fallback to cron expression for arbitrary minute intervals
            $schedule->command('app:cancel-expired-orders')->cron("*/{$minutes} * * * *");
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }
}
