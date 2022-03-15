<?php

namespace App\V1\Observers;

use App\V1\Models\SessionLog;
use VoipMessageQueue;
use App\V1\Services\Voip\Message;
use App\V1\Jobs\Elastic\Report\CallCenter\SessionLogJob;

class SessionLogObserver
{
    /**
     * Listen to saved event
     * 
     * @param SessionLog $model
     */ 
    public function saved(SessionLog $model)
    {
        $this->changeAvailability($model);
    }

    /**
     * Change operator availability
     * 
     * @param SessionLog $model
     */
    protected function changeAvailability($model)
    {
        switch ($model->action) {
            case SessionLog::ACTION_SESSION_START:
            case SessionLog::ACTION_PAUSE_END:
            case SessionLog::ACTION_WRAPUP_END:
                $this->makeAvailable($model->sip);
                break;
            
            case SessionLog::ACTION_SESSION_END:
            case SessionLog::ACTION_PAUSE_START:
            case SessionLog::ACTION_WRAPUP_START:
            case SessionLog::ACTION_CALL_START:
                $this->makeUnavailable($model->sip);
                break;
        }
    }
    
    /**
     * Make SIP available
     * 
     * @param string $sip
     */ 
    protected function makeAvailable($sip)
    {
        VoipMessageQueue::push(new Message(Message::SET_AVAILABLE, [
            'sip' => $sip,
        ]));
    }
    
    /**
     * Make SIP unavailable
     * 
     * @param string $sip
     */ 
    protected function makeUnavailable($sip)
    {
        VoipMessageQueue::push(new Message(Message::SET_UNAVAILABLE, [
            'sip' => $sip,
        ]));
    }
}