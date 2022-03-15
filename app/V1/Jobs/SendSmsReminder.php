<?php

namespace App\V1\Jobs;

use App\V1\Contracts\Services\Messenger\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\V1\Contracts\Repositories\SmsAppointmentReminderRepository;

class SendSmsReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'sms';
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function handle(SmsAppointmentReminderRepository $reminders)
    {
        $smsReminders = $reminders->getUndelivered();

        foreach ($smsReminders as $smsReminder) {
            $smsReminder->sendToPatient();
        }
    }
}
