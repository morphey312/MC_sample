<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

use App\V1\Services\Voip\Message;
use App\V1\Services\Voip\Exception\AmiException;
use Illuminate\Support\Facades\Redis;
use Exception;
use App\V1\Models\Call\CallLog;

trait ProcessMessages
{
    /**
     * Get next message from the queue
     * 
     * @return Message
     */ 
    protected function getNextMessage()
    {
        return $this->messageQueue->pop();
    }
    
    /**
     * Process control message
     * 
     * @param Message
     */ 
    protected function processMessage($message)
    {
        try {
            switch ($message->getType()) {
                case Message::SET_AVAILABLE:
                    $this->changeAvailability($message, true);
                    break;
                
                case Message::SET_UNAVAILABLE:
                    $this->changeAvailability($message, false);
                    break;
                
                case Message::HOLD_CALLER:
                    $this->holdCaller($message);
                    break;
                
                case Message::CREATE_BRIDGE:
                    $this->createBridge($message);
                    break;
                
                case Message::JOIN_CALLER:
                    $this->joinCaller($message);
                    break;
                    
                case Message::KICK_CALLER:
                    $this->kickCaller($message);
                    break;
                
                case Message::REQUEST_STATUS:
                    $this->requestStatus($message);
                    break;
            }
        } catch (Exception $e) {
            $this->sendBackErrorReport($message->getUID(), $e->getMessage());
        }
    }
    
    /**
     * Change caller availability
     * 
     * @param Message $message
     * @param bool $available
     */ 
    protected function changeAvailability($message, $available)
    {
        $sip = $message->getArgument('sip');
        $this->voipManager->setAvailable($sip, $available);
        $this->sendBackSuccessReport($message->getUID());
        $this->trace(sprintf('Agent %s requested status change to %s', 
            $sip,
            $available ? 'AVAILABLE' : 'UNAVAILABLE'
        ));
    }

    /**
     * Check agent availability
     * 
     * @param Message $message
     */
    protected function requestStatus($message)
    {
        $sip = $message->getArgument('sip');
        $this->voipManager->requestMemberStatus($sip);
        $this->sendBackSuccessReport($message->getUID());
        $this->trace(sprintf('Agent %s requested status check', 
            $sip
        ));
    }
    
    /**
     * Hold caller
     * 
     * @param Message $message
     */ 
    protected function holdCaller($message)
    {
        $uid = $message->getArgument('callUid');
        $caller = $message->getArgument('caller');
        $call = $this->getCallFromPool($uid);
        
        if ($call === null) {
            $this->logError(sprintf('Error while trying to hold %s, call %s not exists',
                $caller,
                $uid
            ));
            throw new AmiException('No such call');
        }
        
        $channel = $call->getCallerState($caller, 'channel');
        if ($channel === null) {
            $this->logError(sprintf('Error while trying to hold %s, caller not exists in %s',
                $caller,
                $uid
            ));
            throw new AmiException('No such caller');
        }
        
        try {
            $this->holdRequests->offsetSet($channel, $message->getUID());
            $this->voipManager->parkChannel($channel);
            $this->trace(sprintf('Park request was sent for %s in %s', 
                $caller,
                $uid
            ));
        } catch (Exception $e) {
            $this->logError(sprintf('Error while trying to hold %s at %s: %s',
                $caller,
                $uid,
                $e->getMessage()
            ));
            $this->holdRequests->offsetUnset($channel);
            throw $e;
        }
    }
    
    /**
     * Create bridge
     * 
     * @param Message $message
     */ 
    protected function createBridge($message)
    {
        $uid = $message->getArgument('callUid');
        $call = $this->getCallFromPool($uid);
        
        if ($call === null) {
            $this->logError(sprintf('Error while creating bridge, call %s not exists',
                $uid
            ));
            throw new AmiException('No such call');
        }
        
        $callers = array_keys($call->getRuntimeState('caller', []));
        $channel1 = null;
        $channel2 = null;
        foreach ($callers as $caller) {
            if ($call->getCallerState($caller, 'parked', false) !== false) {
                continue;
            }
            $callerChannel = $call->getCallerState($caller, 'channel');
            if ($callerChannel === $channel1 || $callerChannel === $channel2) {
                // Different caller IDs are related to the channel
                continue;
            }
            if ($channel1 === null) {
                $channel1 = $callerChannel;
                continue;
            }
            if ($channel2 === null) {
                $channel2 = $callerChannel;
                continue;
            }
            $this->logError(sprintf('Error while creating bridge for call %s, too many callers',
                $uid
            ));
            throw new AmiException('Too many callers in this call');
        }
        
        if ($channel1 === null || $channel2 === null) {
            $this->logError(sprintf('Error while creating bridge for call %s, too few callers',
                $uid
            ));
            throw new AmiException('Too few callers in this call');
        }
        
        $conference = $this->voipManager->startConference($channel1, $channel2);
        $call->updateRuntimeState([
            'conference' => $conference,
        ]);
        $call->status = CallLog::STATUS_CONFERENCE;
        $this->putCallToPool($call);

        $this->sendBackSuccessReport($message->getUID(), [
            'conference' => $conference,
        ]);

        $this->trace(sprintf('Bridge was requested for %s, %s in %s', 
            $channel1, 
            $channel2,
            $uid
        ));
    }
    
    /**
     * Join caller to the conference
     * 
     * @param Message $message
     */ 
    protected function joinCaller($message)
    {
        $caller = $message->getArgument('caller');
        $srcUid = $message->getArgument('srcCallUid');
        $destUid = $message->getArgument('destCallUid');
        
        $srcCall = $this->getCallFromPool($srcUid);
        if ($srcCall === null) {
            $this->logError(sprintf('Error while joining %s, source call %s not exists',
                $caller,
                $srcUid
            ));
            throw new AmiException('No such source call');
        }
        
        $channel = $srcCall->getCallerState($caller, 'channel');
        if ($channel === null) {
            $this->logError(sprintf('Error while joining %s, caller not exists in %s',
                $caller,
                $srcUid
            ));
            throw new AmiException('No such caller');
        }
        
        $destCall = $this->getCallFromPool($destUid);
        if ($destCall === null) {
            $this->logError(sprintf('Error while joining %s, destination call %s not exists',
                $caller,
                $destUid
            ));
            throw new AmiException('No such destination call');
        }
        
        $conference = $destCall->getRuntimeState('conference');
        if ($conference === null) {
            $this->logError(sprintf('Error while joining %s, destination call %s is not a conference',
                $caller,
                $destUid
            ));
            throw new AmiException('Destination call is not a conference');
        }
        
        $this->voipManager->joinConference($channel, $conference);
        $this->sendBackSuccessReport($message->getUID());
        $this->trace(sprintf('Join request was sent to join %s (%s) to conference %s', 
            $caller,
            $srcUid,
            $destUid
        ));
    }
    
    /**
     * Kick caller from the conference
     * 
     * @param Message $message
     */ 
    protected function kickCaller($message)
    {
        $caller = $message->getArgument('caller');
        $callUid = $message->getArgument('callUid');
        $targetCallUid = $message->getArgument('targetCallUid');
        
        $targetCall = $this->getCallFromPool($targetCallUid);
        if ($targetCall === null) {
            $this->logError(sprintf('Error while removing %s, target call %s not exists',
                $caller,
                $targetCallUid
            ));
            throw new AmiException('No such target call');
        }
        
        $channel = $targetCall->getCallerState($caller, 'channel');
        if ($channel === null) {
            $this->logError(sprintf('Error while removing %s, caller not exists in %s',
                $caller,
                $targetCallUid
            ));
            throw new AmiException('No such caller');
        }
        
        $call = $this->getCallFromPool($callUid);
        if ($call === null) {
            $this->logError(sprintf('Error while removing %s, call %s not exists',
                $caller,
                $callUid
            ));
            throw new AmiException('No such call');
        }
        
        $conference = $call->getRuntimeState('conference');
        if ($conference === null) {
            $this->logError(sprintf('Error while removing %s, call %s is not a conference',
                $caller,
                $callUid
            ));
            throw new AmiException('Call is not a conference');
        }
        
        $this->voipManager->hangup($channel);
        $this->sendBackSuccessReport($message->getUID());
        $this->trace(sprintf('Kick request was sent to remove %s (%s) from conference %s', 
            $caller,
            $targetCallUid,
            $callUid
        ));
    }
    
    /**
     * Send back success report
     * 
     * @param string $uid
     * @param array $data
     */ 
    protected function sendBackSuccessReport($uid, $data = [])
    {
        $this->messageQueue->reportBack(new Message(Message::REPORT_MESSAGE, [
            Message::UID_KEY => $uid,
            'success' => true,
            'data' => $data,
        ]));
    }
    
    /**
     * Send back error report
     * 
     * @param string $uid
     * @param string $error
     * @param array $data
     */ 
    protected function sendBackErrorReport($uid, $error, $data = [])
    {
        $this->messageQueue->reportBack(new Message(Message::REPORT_MESSAGE, [
            Message::UID_KEY => $uid,
            'success' => false,
            'reason' => $error,
            'data' => $data,
        ]));
    }
}