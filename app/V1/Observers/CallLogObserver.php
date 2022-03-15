<?php

namespace App\V1\Observers;

use App\V1\Facades\Messenger;
use App\V1\Models\Call\CallLog;
use App\V1\Models\Notification\Template;
use App\V1\Repositories\Notification\TemplateRepository;
use App\V1\Sms\Calls\MissedCall;
use Illuminate\Support\Facades\Auth;

class CallLogObserver
{   
    const MOBILE_PHONES = [
        '050','066','095','099',
        '067','068','096','097','098',
        '063','073','093',
        '091','092','094'
    ];
    /**
     * @param CallLog $model
     */
    public function updated(CallLog $model) 
    {
        if($model->isDirty('status') &&
            $model->status === CallLog::STATUS_ABANDONED &&
            in_array(mb_substr($model->phone_number,0,3), self::MOBILE_PHONES)
        ) { 
            $templateRepository = app()->make(TemplateRepository::class);
            $template = $templateRepository->getTemplateByScenario(Template::SCENARIO_SMS_MISSED_CALL);
            if ($template) {
                $queues = $template->voip_queue->pluck('queue')->toArray();
                if (in_array($model->queue, $queues)) {
                    $model->queueSms($template, now());
                    Messenger::sendWithTemplate(new MissedCall($model), $template);
                }
            }
        }
    }
}
