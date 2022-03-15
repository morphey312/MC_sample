<?php

namespace App\V1\Console;

use App\V1\Jobs\SendSmsReminder;
use App\V1\Jobs\Report\Telegram\CallsTelegramJob;
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

    ];

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

    /**
     * Define the application's command schedule.
     * App\Console\Kernel
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->importMedicineRests($schedule);
        $this->cleanupAssignments($schedule);
        $this->updateRedirectReportIndices($schedule);
//        todo fix after testing
//        $this->sendScheduledSMS($schedule);
        $this->getSentSmsStatuses($schedule);
        $this->exportSessionLogs($schedule);
        $this->checkEhealth($schedule);
        $this->getExchange($schedule);
        $this->sendTelegramReport($schedule);

        //eSputnik
        $this->sendMailing($schedule);

        $schedule->command('horizon:snapshot')->everyTenMinutes();

    }

    /**
     * Clean up assigned but not used services and analyses
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function cleanupAssignments($schedule)
    {
        $schedule->command('assignments:cleanup')->dailyAt('02:00');
    }

    /**
     * Run import 1c medicine rests command chain
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function importMedicineRests($schedule)
    {
        if (config('services.one_s.enable_import_rests')) {
            $schedule->command('import:stores')->cron('0 */2 * * *');
            $schedule->command('import:firms')->cron('0 */2 * * *');
            $schedule->command('import:medicines')->cron('0 */2 * * *');
            $schedule->command('import:medicine_rests')->cron('0 */2 * * *');
        }
    }

    /**
     * Update redirects report elastic indices
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function updateRedirectReportIndices($schedule)
    {
        $schedule->command('elastic-export:redirects_all')->dailyAt('03:00');
    }

    /**
     * Send Scheduled SMS
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function sendScheduledSMS($schedule)
    {
        $schedule->job(new SendSmsReminder)->everyMinute()->withoutOverlapping(15);
    }

    /**
     * Get sent sms statuses
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function getSentSmsStatuses($schedule)
    {
        $schedule->command('sms:get-delivery-statuses')->everyTenMinutes()->withoutOverlapping(10);
    }

    /**
     * Export previous day operators session logs to elastic
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function exportSessionLogs($schedule)
    {
        $schedule->command('elastic-export:previous_day_wrapups')->dailyAt('01:30');
    }

    /**
     * Check MSP status in eHealth
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function checkEhealth($schedule)
    {
        $schedule->command('ehealth:check-status')->hourly();
    }

    /**
     * Get currency exchange rate
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     */
    protected function getExchange($schedule)
    {
        $schedule->command('exchange:get-rates')->dailyAt('00:03');
    }

    /** Send Mailing ScheduleTemplates
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function sendMailing (Schedule $schedule)
    {
        if (config('app.env') === 'test') {
            $schedule->command('mailing-templates:send')->everyFifteenMinutes();
        } else {
            $schedule->command('mailing-templates:send')->dailyAt('2:00');
        }
    }


    /**
     * Send calcenters report by telegram bot
     */
    protected function sendTelegramReport($schedule)
    {
        if(config('botman.telegram.enable')) {
            $schedule->job(new CallsTelegramJob)->everyMinute();
        }

    }
}
