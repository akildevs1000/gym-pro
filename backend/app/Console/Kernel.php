<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $date = null;

    protected function schedule(Schedule $schedule)
    {
        $this->date = date('Y-m-d');

        $date = date('Y-m-d');

        $schedule->command('birthday:wish')->dailyAt('00:00');

        $schedule->command('memberships:expire-today')->dailyAt('00:15');

        $schedule
            ->command('task:sync_attendance_logs')
            ->everyMinute();

        $schedule
            ->command('task:sync_attendance_ox900_logs')
            ->everyMinute();
        $schedule
            ->command('task:update_company_ids')
            ->everyMinute();

        $schedule
            ->command("task:sync_member_report $date")
            ->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
