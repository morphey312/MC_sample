<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\MessengerService as MessengerServiceInterface;
use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Contracts\Repositories\Notification\TemplateRepository;
use App\V1\Models\Notification\Template;
use App\V1\Models\Notification\Channel;
use App\V1\Models\Patient;
use App\V1\Models\Patient\User as PatientUser;
use App\V1\Jobs\Messenger\SmtpJob;
use App\V1\Jobs\Messenger\SmsJob;
use App\V1\Models\Call\CallLog;
use App\V1\Models\Employee;
use Exception;
use Illuminate\Support\Facades\Log;
use App\V1\Traits\PhoneNumber;

class MessengerService implements MessengerServiceInterface
{
    use PhoneNumber;

    /**
     * @var TemplateRepository
     */
    protected $templates;

    /**
     * Service constructor
     *
     * @param TemplateRepository $templates
     */
    public function __construct(TemplateRepository $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @inherit
     */
    public function send(Message $message)
    {
        $templates = $this->templates->getUsableTemplates(
            $message->getScenario(),
            $message->getClinicId()
        );
        $wasSent = false;

        foreach ($templates as $template) {
            if ($this->sendWithTemplate($message, $template)) {
                $wasSent = true;
            }
        }

        return $wasSent;
    }

    /**
     * Send message using particular template
     *
     * @param Message $message
     * @param Template $template
     *
     * @return bool
     */
    public function sendWithTemplate($message, $template)
    {
        try {
            switch ($template->channel->type) {
                case Channel::TYPE_EMAIL:
                    return $this->sendViaSmtp($message, $template);
                    break;
                case Channel::TYPE_SMS:
                    return $this->sendViaSms($message, $template);
            }
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }

        return false;
    }

    /**
     * Send message using SMTP
     *
     * @param Message $message
     * @param Template $template
     *
     * @return bool
     */
    protected function sendViaSmtp($message, $template)
    {
        $address = $this->getEmailAddress($message->getRecipient());
        if ($address === null) {
            return false;
        }

        SmtpJob::dispatch($address, clone $message, clone $template);

        return true;
    }

    /**
     * Send message using SMS
     *
     * @param Message $message
     * @param Template $template
     *
     * @return bool
     */
    protected function sendViaSms($message, $template)
    {
        $number = $this->getPhoneNumber($message->getRecipient());

        if ($number === null) {
            return false;
        }

        SmsJob::dispatch($number, clone $message, clone $template);

        return true;
    }

    /**
     * Extract recipient email address
     *
     * @param mixed $recipient
     *
     * @return string
     */
    protected function getEmailAddress($recipient)
    {
        if ($recipient instanceof Patient) {
            $contact = $recipient->email;
            return $contact === null ? null : $contact->value;
        }

        return null;
    }

    /**
     * Extract recipient phone number
     *
     * @param mixed $recipient
     *
     * @return string
     */
    protected function getPhoneNumber($recipient)
    {
        if ($recipient instanceof Patient) {
            $contact = $recipient->primary_phone;
            return $contact === null ? null : $this->normalizePhoneNumber($contact->value);
        }

        if ($recipient instanceof Employee) {
            return $recipient->phone === null ? null : $this->normalizePhoneNumber($recipient->phone);
        }

        if ($recipient instanceof PatientUser) {
            return $this->normalizePhoneNumber($recipient->phone);
        }

        if ($recipient instanceof CallLog) {
            return $this->normalizePhoneNumber($recipient->phone_number);
        }
        return null;
    }
}
