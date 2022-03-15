<?php

namespace App\V1\Mailing\Analysis;

use App\V1\Models\Analysis\Result;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Services\Messenger\Message;
use Illuminate\Support\Facades\Log;

class AnalysisResultSavedMessage extends Message
{
    /**
     * @var Appointment
     */
    protected $result;
    protected $isDirty;


    /**
     * Constructor
     *
     * @param Result $result
     */
    public function __construct(Result $result)
    {
        $this->result = $result;
        $this->isDirty = $result->isDirty();
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
        return MailingTemplate::SCENARIO_EMAIL_STATUS;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        return $this->result->clinic_id;
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
    public function getResult()
    {
        return $this->result;
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
