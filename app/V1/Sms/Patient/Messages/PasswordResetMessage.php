<?php

namespace App\V1\Sms\Patient\Messages;

use App\V1\Models\Patient\User;
use App\V1\Services\Messenger\Message;
use App\V1\Models\Notification\Template;

class PasswordResetMessage extends Message
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * Constructor
     * @param User $user
     * @param $password
     */
    public function __construct(User $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return PasswordResetMessage
     */
    public function build()
    {
        $this->compose([
            'password' => $this->password,
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return Template::SCENARIO_SMS_NEW_PASSWORD_FOR_PATIENT;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        return $this->user->primary_clinic_id;
    }
}
