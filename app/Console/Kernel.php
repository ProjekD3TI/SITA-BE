<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\Admin\AdminPengelola\PeminjamanInventarisController;
use App\Http\Controllers\Admin\AdminPengelola\PeminjamanBhpController;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function() {
            app()->make(PeminjamanInventarisController::class)
                ->sendOverdueReminders();
        // })->daily()->at('09:00');
        })->everyMinute(); // Untuk testing,

         $schedule->call(function() {
            app()->make(PeminjamanBhpController::class)
                ->sendOverdueReminders();
        })->everyMinute();
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
