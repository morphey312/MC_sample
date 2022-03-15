<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class SmsAppointmentReminderPolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'sms-reminders';
}
