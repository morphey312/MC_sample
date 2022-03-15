<?php

namespace App\V1\Sms\Calls;

use App\V1\Models\Call\CallLog;
use App\V1\Services\Messenger\Message;
use App\V1\Models\Notification\Template;
use Illuminate\Support\Arr;

class MissedCall extends Message
{
    /**
     * @var CallLog
     */
    protected $callLog;

    /**
     * Constructor
     * @param User $user
     * @param $password
     */
    public function __construct(CallLog $callLog)
    {
        $this->callLog = $callLog;
    }

    /**
     * Build the message.
     *
     * @return NewUserPasswordMessage
     */
    public function build()
    {   
        $this->compose([]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        return $this->callLog;
    }
    /**
     * @inheritdoc
     */
    public function afterDelivery($success, $number, $result = null)
    {   
        if ($success) {
            $this->callLog->sms_reminders->status = Message::STATUS_DELIVERING;
        } else {
            $this->callLog->sms_reminders->status = Message::STATUS_DELIVERY_FAILED;
        }
        if (is_array($result)) {
            $this->callLog->sms_reminders->vendor_id = Arr::get($result, 'results.requestId');
            $this->callLog->sms_reminders->vendor_data = Arr::get($result, 'results');
        }
    
        $this->callLog->sms_reminders->save();
    }
    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return Template::SCENARIO_SMS_MISSED_CALL;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        return null;
    }
}
