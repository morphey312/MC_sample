<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\MailingService as MessengerServiceInterface;
use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Contracts\Repositories\Notification\MailingTemplateRepository;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Models\Notification\MailingProvider;
use Exception;
use Illuminate\Support\Facades\Log;

class MailingService implements MessengerServiceInterface
{
    /**
     * @var MailingTemplateRepository
     */
    protected $mailingTemplates;

    /**
     * Service constructor
     *
     * @param MailingTemplateRepository $mailingTemplates
     */
    public function __construct(MailingTemplateRepository $mailingTemplates)
    {
        $this->mailingTemplates = $mailingTemplates;
    }

    /**
     * @inherit
     */
    public function send(Message $message = null)
    {
        if (empty($message)) {
            $mailingTemplates = $this->mailingTemplates->getScheduleTemplates();
            $message = null;
        } else {
            $mailingTemplates = $this->mailingTemplates->getUsableTemplates(
                $message->getScenario(),
                $message->getClinicId()
            );
        }
        // todo remove after test
        Log::channel('esputnik_test')->info('send', [
            'empty($message)' => empty($message),
            '$mailingTemplates' => $mailingTemplates,
        ]);

        $wasSent = false;

        foreach ($mailingTemplates as $template) {
            if ($this->sendWithTemplate($message, $template)) {
                $wasSent = true;
            }
        }

        return $wasSent;
    }

    /**
     * Send message using particular template
     *
     * @param Message|null $message
     * @param MailingTemplate $mailingTemplate
     *
     * @return bool
     */
    public function sendWithTemplate($message, $mailingTemplate)
    {
        try {
            switch ($mailingTemplate->provider->type) {
                case MailingProvider::TYPE_ESPUTNIK:
                    return $this->sendViaEsputnik($message, $mailingTemplate);
            }
        } catch (Exception $e) {
            Log::warning($e->getMessage());
        }

        return false;
    }

    /**
     * Send message using SMTP
     *
     * @param Message|null $message
     * @param MailingTemplate $mailingTemplate
     *
     * @return bool
     */
    protected function sendViaEsputnik($message, $mailingTemplate)
    {
        $job=resolve($mailingTemplate->scenario);

        Log::channel('esputnik_test')->info('sendViaEsputnik', [
            '$job' => $job
        ]);

        $job::dispatch($mailingTemplate, $message)
            ->delay(now()->addMinutes(5));

        return true;
    }
}
