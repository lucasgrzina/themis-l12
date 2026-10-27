<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Archivado automatico de tramites con Beneficio Acordado (AFJP + ANSES)
        // antiguos. Corre todos los dias a las 00:00.
        $schedule->command('tramites:archivar-beneficio-acordado')
                 ->dailyAt('00:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/archivado-tramites.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
