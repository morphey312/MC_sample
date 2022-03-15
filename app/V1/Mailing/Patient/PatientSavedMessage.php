<?php

namespace App\V1\Mailing\Patient;

use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Patient;
use App\V1\Services\Messenger\Message;

class PatientSavedMessage extends Message
{
    /**
     * @var Appointment
     */
    protected $patient;
    protected $isDirty;


    /**
     * Constructor
     *
     * @param Patient $patient
     */
    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
        $this->isDirty = $patient->isDirty(['firstname', 'birthday', 'gender', 'is_confirmed']);
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
        return MailingTemplate::SCENARIO_CONFIRMED_DATA;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        $clinic = $this->patient->getClinicOfContact($this->patient->contact_details['primary_phone_number']);

        if (isset($clinic)) {
            return $clinic->id;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @inheritdoc
     */
    public function isDirty()
    {
        return $this->isDirty;
    }

    /**
     * @inheritdoc
     */
    public function beforeSend($to, $template)
    {
        //
    }
}
