<?php

namespace App\V1\Mailing\Appointments;

use App\V1\Models\Appointment;
use App\V1\Services\Messenger\Message;
use App\V1\Models\Notification\MailingTemplate;
use Illuminate\Support\Facades\Log;

class AppointmentMessage extends Message
{
    /**
     * @var Appointment
     */
    public $appointment;

    /**
     * Constructor
     *
     * @param Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        //todo remove after test

        Log::channel('esputnik_test')->info('AppointmentMessage', [
            '$this->appointment' => $this->appointment,
        ]);
    }

    public function build()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return MailingTemplate::SCENARIO_APPOINTMENT;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        // todo remove after test
        Log::channel('esputnik_test')->info('AppointmentMessage', [
            'clinic_id' => $this->appointment->clinic_id,
        ]);

        return $this->appointment->clinic_id;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getAppointment()
    {
        return $this->appointment;
    }

    /**
     * @inheritdoc
     */
    public function beforeSend($to, $template)
    {
        //
    }
}
