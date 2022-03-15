<?php

namespace App\V1\Mail\Analysis;

use App\V1\Models\Analysis\Result as AnalysisResult;
use App\V1\Models\EmailLog;
use App\V1\Models\Notification\Template;
use App\V1\Services\Messenger\Message;
use Illuminate\Support\Facades\Log;

class SecureResult extends Message
{
    /**
     * @var AnalysisResult
     */
    protected $result;

    /**
     * Constructor
     *
     * @param AnalysisResult $result
     */
    public function __construct(AnalysisResult $result)
    {
        $this->result = $result;
    }

    /**
     * Build the message.
     *
     * @return SecureResult
     */
    public function build()
    {
        $this->compose([
            'patientName' => $this->result->patient->full_name,
            'analysisName' => $this->result->analysis->name,
            'password' => substr($this->result->patient->primary_phone->value, -4),
            'cutNumber' => substr($this->result->patient->primary_phone->value, 0, strlen($this->result->patient->primary_phone->value) - 4)
        ], $this->result->attachments, true)->withSwiftMessage(function ($message) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-SES-CONFIGURATION-SET', env('SES_CONFIG', 'MCPlus'));
            $headers->addTextHeader('MC-EMAIL-TYPE', 'analysis');
            $headers->addTextHeader('MC-ANALYSIS-ID', $this->result->id);
        });
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return Template::SCENARIO_SECURE_ANALYSIS_RESULT;
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
        return $this->result->patient;
    }

    /**
     * @inheritdoc
     */
    public function beforeSend($to, $template)
    {
        try {
            $fromData = $this->getEmailFromField($template->channel);
            $from = $fromData['from'] . ' ' . $fromData['name'];

            $emailHtml = view(
                $this->viewName,
                $template->compose([
                    'patientName' => $this->result->patient->full_name,
                    'analysisName' => $this->result->analysis->name,
                    'password' => substr($this->result->patient->primary_phone->value, -4),
                    'cutNumber' => substr($this->result->patient->primary_phone->value, 0, strlen($this->result->patient->primary_phone->value) - 4)
                ])
            )->render();

            $model = new EmailLog();
            $model->event = Message::STATUS_PREPARED;
            $model->from = $from;
            $model->to = $to;
            $model->subject = $template->subject;
            $model->target_type = AnalysisResult::EMAIL_TYPE;
            $model->target_id = $this->result->id;
            $model->event_data = [
                'html' => $emailHtml
            ];
            $model->attachments = $this->result->attachments;

            $model->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
