<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

use App\V1\Models\Call\CallLog;
use App\V1\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\V1\Contracts\Services\VoipService;
use App\V1\Events\Broadcast\CallerHangup;
use App\V1\Events\Broadcast\MemberPause;
use App\V1\Services\Voip\Exception\AmiException;
use App\V1\Services\Voip\Exception\DisconnectException;
use Exception;

trait HandleEvents
{
    /**
     * Register events listeners
     */
    protected function setupListeners()
    {
        $this->voipManager->onNewchannel([$this, 'onNewchannel']);
        $this->voipManager->onQueueCallerJoin([$this, 'onQueueCallerJoin']);
        $this->voipManager->onQueueCallerAbandon([$this, 'onQueueCallerAbandon']);
        $this->voipManager->onAgentConnect([$this, 'onAgentConnect']);
        $this->voipManager->onBridgeEnter([$this, 'onBridgeEnter']);
        $this->voipManager->onHangup([$this, 'onHangup']);
        $this->voipManager->onParkedCall([$this, 'onParkedCall']);
        $this->voipManager->onUnParkedCall([$this, 'onUnParkedCall']);
        $this->voipManager->onParkedCallGiveUp([$this, 'onParkedCallGiveUp']);
        $this->voipManager->onConfbridgeJoin([$this, 'onConfbridgeJoin']);
        $this->voipManager->onConfbridgeLeave([$this, 'onConfbridgeLeave']);
        $this->voipManager->onQueueMemberPause([$this, 'onQueueMemberPause']);
        $this->voipManager->onQueueMember([$this, 'onQueueMember']);
    }
    
    /**
     * Process AMI events
     */ 
    protected function processAmiEvents()
    {
        try {
            return $this->voipManager->process();
        } catch (DisconnectException $e) {
            $this->tryReconnect();
        } catch (AmiException $e) {
            $this->logError($e->getMessage());
        }
        
        return 0;
    }
    
    /**
     * Try to reconnect to AMI server
     */ 
    protected function tryReconnect()
    {
        $this->logError('Asterisk server was disconnected, trying to reconnect');
        
        while ($this->shouldRun) {
            sleep(self::RECONNECT_DELAY);
            try {
                $this->voipManager->connect();
                $this->trace('Successfully reconnected to Asterisk server');
                return;
            } catch (Exception $e) {
            }
        }
    }
    
    /**
     * Resolve the call UID (always first in sequence).
     * 
     * @param array $caller
     * 
     * @return string
     */ 
    protected function resolveCallUid($caller)
    {
        return strcmp($caller['uid'], $caller['linkedId']) > 0 
            ? $caller['linkedId'] 
            : $caller['uid'];
    }
    
    /**
     * Check if caller is the originator of the call
     * 
     * @param array $caller
     * 
     * @return bool
     */ 
    protected function isOriginator($caller)
    {
        return $caller['uid'] === $this->resolveCallUid($caller);
    }
    
    /**
     * Detect simple bridge
     * 
     * @param string $technology
     * 
     * @return bool
     */ 
    protected function isSimpleBridge($technology)
    {
        return $technology === 'simple_bridge';
    }
    
    /**
     * Check if number provided
     * 
     * @param string $number
     * 
     * @return bool
     */ 
    protected function isNumberProvided($number) 
    {
        return !empty($number) && $number !== '<unknown>';
    }

    /**
     * Normalize phone number format
     * 
     * @param string $number
     * 
     * @return string
     */
    protected function normalizePhoneNumber($number)
    {
        $plus = strpos($number, '+');
        
        if ($plus !== false) {
            // remove clinic prefix
            $number = substr($number, $plus + 1);
        }

        $strlen = strlen($number);
        if ($strlen === 12 && substr($number, 0, 3) === '380') {
            // Ukraine full format
            return substr($number, 2);
        }
        
        // Other countries/Ukraine short format
        return $number;
    }
    
    /**
     * Check if the call has online callers
     * 
     * @param CallLog $call
     * 
     * @return bool
     */ 
    protected function hasOnlineChannels($call)
    {
        return $call->getRuntimeState('channels', []) !== [];
    }
    
    /**
     * Mark channel as online (offline)
     * 
     * @param CallLog $call
     * @param string $channel
     * @param bool $online
     */ 
    protected function setChannelOnline($call, $channel, $online)
    {
        $channels = $call->getRuntimeState('channels', []);
        
        if ($online) {
            $channels[] = $channel;
        } else {
            $channels = array_diff($channels, [$channel]);
        }
        
        $call->updateRuntimeState([
            'channels' => array_unique($channels),
        ]);
    }
    
    /**
     * Handle new channel event
     * 
     * Case: someone has been connected to the line. At this point
     *      new call gets initiated, considering the conditions below 
     *      are met:
     *      - the channel is originator of the call;
     *      - the called extension is not a parking lot.
     * 
     * @param array $params
     */ 
    public function onNewchannel($params)
    {
        $uid = $this->resolveCallUid($params);
        
        $this->trace(sprintf('Newchannel: %s (%s), context = %s, extension = %s, UID = %s', 
            $params['number'], 
            $params['channel'],
            $params['context'],
            $params['extension'],
            $uid
        ));
        
        if ($this->isOriginator($params) === false || 
            $this->isParkinglotExtension($params['extension'])) {
            return;
        }

        $call = new CallLog();
        $call->status = CallLog::STATUS_INITIATED;
        $call->started_at = Carbon::now();
        $call->uid = $uid;

        // Determining call type
        switch ($params['source']) {
            case VoipService::SRC_INCOMING_CALL:
            default:
                $callerNumber = $this->normalizePhoneNumber($params['number']);
                $call->type = CallLog::TYPE_INCOMING;
                $call->phone_number = $callerNumber;
                $call->caller_number = $callerNumber;
                $call->source = CallLog::SOURCE_CALL;
                break;
            case VoipService::SRC_OUTGOING_CALL:
                $callerNumber = $this->normalizePhoneNumber($params['number']);
                $calleeNumber = $this->normalizePhoneNumber($params['extension']);
                $call->phone_number = $calleeNumber;
                $call->caller_number = $callerNumber;
                $call->type = CallLog::TYPE_OUTGOING;
                $call->source = CallLog::SOURCE_INTERNAL;
                break;
            case VoipService::SRC_CALLBACK:
                $callerNumber = $this->normalizePhoneNumber($params['extension']);
                $call->phone_number = $callerNumber;
                $call->caller_number = $callerNumber;
                $call->type = CallLog::TYPE_OUTGOING;
                $call->source = CallLog::SOURCE_CALLBACK;
                break;
            case VoipService::SRC_SITE_CALLBACK:
                $callerNumber = $this->normalizePhoneNumber($params['extension']);
                $call->phone_number = $callerNumber;
                $call->caller_number = $callerNumber;
                $call->type = CallLog::TYPE_OUTGOING;
                $call->source = CallLog::SOURCE_WEBCALLBACK;
                break;
        }

        // Trying to resolve caller identity
        if ($this->isNumberProvided($call->caller_number)) {
            $callerInfo = $this->subResolver->resolve($call->caller_number);
            if ($callerInfo['subject']) {
                $caller = $callerInfo['subject'];
                $call->caller = $caller;
                if ($caller instanceof Patient) {
                    $call->clinic = $caller->getClinicOfContact($call->caller_number);
                    $call->patient = $caller;
                }
            }
        }

        //  For outgoing call, trying to resolve callee identity
        if ($params['source'] === VoipService::SRC_OUTGOING_CALL &&
            $this->isNumberProvided($call->phone_number)) {
            $calleeInfo = $this->subResolver->resolve($call->phone_number);
            if ($calleeInfo['subject']) {
                $callee = $calleeInfo['subject'];
                $call->callee = $callee;
                if ($callee instanceof Patient) {
                    $call->clinic = $callee->getClinicOfContact($call->phone_number);
                    $call->patient = $callee;
                }
            }
        }
        
        // Remember, which channel caller is connected to
        $call->updateCallerState($call->caller_number, [
            'channel' => $params['channel'],
        ]);

        // Mark channel of caller as "online"
        $this->setChannelOnline($call, $params['channel'], true);

        // Put call to the pool
        $this->putCallToPool($call);
    }
    
    /**
     * Handle join queue event
     * 
     * Case: caller joins the queue. At this point call status gets 
     *      changed to STATUS_WAITING
     * 
     * @param array $params
     */ 
    public function onQueueCallerJoin($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('QueueCallerJoin: %s (%s), extension = %s, queue = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['caller']['extension'],
            $params['queue'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        
        if ($call != null) {
            // Change call status to "waiting"
            $call->status = CallLog::STATUS_WAITING;
            $call->clinic_id = $this->getQueueClinic($params['queue']);
            $call->queue = $params['queue'];
            $call->extension = $params['caller']['extension'];
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle abandon queue event
     * 
     * Case: caller abandons the queue. At this point call status gets 
     *      changed to STATUS_ABANDONED
     * 
     * @param array $params
     */ 
    public function onQueueCallerAbandon($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('QueueCallerAbandon: %s (%s), queue = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['queue'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        
        if ($call != null) {
            // Change call status to "abandoned"
            $call->status = CallLog::STATUS_ABANDONED;
            $call->ended_at = Carbon::now();
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle agent connect event
     * 
     * Case: agent starts serve a call. At this point we can store
     *      agent details in the call
     * 
     * @param array $params
     */ 
    public function onAgentConnect($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('AgentConnect: %s => %s, callerChannel = %s, UID = %s', 
            $params['agent']['channel'],
            $params['caller']['number'], 
            $params['caller']['channel'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        
        if ($call != null) {
            $number = $this->normalizePhoneNumber($params['agentNumber']);

            if ($call->callee_id == null) {
                // Resolve agent identity
                $calleeInfo = $this->subResolver->resolve($number);
                $callee = $calleeInfo['subject'];
                if ($callee) {
                    $call->callee = $callee;
                }
            }
            
            // Remember, which channel agent is connected to
            $call->updateCallerState($number, [
                'channel' => $params['agent']['channel'],
            ]);

            // Mark agent channel as "online"
            $this->setChannelOnline($call, $params['agent']['channel'], true);
            
            // Put call to pool
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle bridge enter event
     * 
     * Case: originator of the call enters the bridge. At this point
     *      call status gets changed to STATUS_IN_PROGRESS
     * 
     * @param array $params
     */ 
    public function onBridgeEnter($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('BridgeEnter: %s (%s), type = %s, bridgeID = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['technology'],
            $params['bridgeId'],
            $uid
        ));

        if ($this->isSimpleBridge($params['technology']) === false) {
            return;
        }
        
        $call = $this->getCallFromPool($uid);
        
        if ($call != null) {
            $number = $this->normalizePhoneNumber($params['caller']['number']);

            // Change call status to "progress"
            $call->status = CallLog::STATUS_IN_PROGRESS;

            // Remember, which channel caller is connected to
            $call->updateCallerState($number, [
                'channel' => $params['caller']['channel'],
            ]);

            // Mark channel of caller as "online"
            $this->setChannelOnline($call, $params['caller']['channel'], true);

            // Put channel to pool
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle hangup event
     * 
     * Case: someone hanged up. At this point we can change caller 
     *      status as "offline". If all callers are offline, call
     *      status gets changed to STATUS_ENDED (if not abandoned)
     * 
     * @param array $params
     */ 
    public function onHangup($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('Hangup: %s (%s), reason = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['reason'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        $number = $this->normalizePhoneNumber($params['caller']['number']);
        
        if ($call != null) {
            // Mark caller channel status to "offline"
            $this->setChannelOnline($call, $params['caller']['channel'], false);

            if (!$this->hasOnlineChannels($call)) {
                // Remove call from pool if last participant has gone
                if ($call->status != CallLog::STATUS_ABANDONED) {
                    $call->status = CallLog::STATUS_ENDED;
                    $call->ended_at = Carbon::now();
                }
                $this->callLogs->persist($call);
                $this->removeCallFromPool($call->uid);
            } else {
                // Put call to pool
                $this->putCallToPool($call);
            }
        }
        
        broadcast(new CallerHangup($number));
    }
    
    /**
     * Handle parked call event
     * 
     * Case: call was parked. At this point call status 
     *      gets changed to STATUS_PARKED. Also we can send back
     *      a report about channel was parked.
     * 
     * @param array $params
     */ 
    public function onParkedCall($params)
    {
        $uid = $this->resolveCallUid($params['parkee']);
        
        $this->trace(sprintf('ParkedCall: %s (%s), lot = %s, space = %s, UID = %s', 
            $params['parkee']['number'], 
            $params['parkee']['channel'],
            $params['lot'],
            $params['space'],
            $uid
        ));
        
        $channel = $params['parkee']['channel'];
        $lot = Arr::only($params, ['lot', 'space']);
        $number = $this->normalizePhoneNumber($params['parkee']['number']);
        
        if ($this->holdRequests->offsetExists($channel)) {
            // Report that call was successfully parked
            $reportUid = $this->holdRequests->offsetGet($channel);
            $this->holdRequests->offsetUnset($channel);
            $this->sendBackSuccessReport($reportUid, $lot);
        }
        
        $call = $this->getCallFromPool($uid);
        
        if ($call !== null) {
            // Remember lot caller is parked to 
            // and change call status to "parked"
            $call->updateCallerState($number, [
                'parked' => $lot,
            ]);
            $call->status = CallLog::STATUS_PARKED;
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle unparked call event
     * 
     * Case: call was unparked by making a call to parking lot. At this 
     *      point call status gets changed to STATUS_IN_PROGRESS.
     * 
     * @param array $params
     */ 
    public function onUnParkedCall($params)
    {
        $uid = $this->resolveCallUid($params['parkee']);
        
        $this->trace(sprintf('UnParkedCall: %s (%s), retriever = %s, UID = %s', 
            $params['parkee']['number'], 
            $params['parkee']['channel'],
            $params['retriever']['channel'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        $number = $this->normalizePhoneNumber($params['parkee']['number']);
        
        if ($call !== null) {
            // Clear lot caller is parked to 
            $call->updateCallerState($number, [
                'parked' => null,
            ]);

            // and change call status to "progress"
            $call->status = CallLog::STATUS_IN_PROGRESS;
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle parked call giveup event
     * 
     * Case: call was parked but given up. At this 
     *      point call status gets changed to STATUS_ABANDONED.
     * 
     * @param array $params
     */ 
    public function onParkedCallGiveUp($params)
    {
        $uid = $this->resolveCallUid($params['parkee']);
        
        $this->trace(sprintf('ParkedCallGiveUp: %s (%s), lot = %s, space = %s, UID = %s', 
            $params['parkee']['number'], 
            $params['parkee']['channel'],
            $params['lot'],
            $params['space'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        
        if ($call !== null) {
            $number = $this->normalizePhoneNumber($params['parkee']['number']);

            // Clear lot caller is parked to 
            $call->updateCallerState($number, [
                'parked' => null,
            ]);

            // Change call status to "abandoned"
            $call->status = CallLog::STATUS_ABANDONED;
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle confbridge join event
     * 
     * Case: someone joins a conference. At this point call status 
     *      gets changed to STATUS_CONFERENCE.
     * 
     * @param array $params
     */ 
    public function onConfbridgeJoin($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('ConfbridgeJoin: %s (%s), confID = %s, bridgeID = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['conference'],
            $params['bridgeId'],
            $uid
        ));
        
        $call = $this->getCallFromPool($uid);
        $number = $this->normalizePhoneNumber($params['caller']['number']);
        
        if ($call !== null && $call->status != CallLog::STATUS_CONFERENCE) {
            // Remember conference number for this call
            $call->updateRuntimeState([
                'conference' => $params['conference'],
            ]);

            // Clear lot caller is parked to if any
            $call->updateCallerState($number, [
                'parked' => null,
            ]);

            // Change call status to "conference"
            $call->status = CallLog::STATUS_CONFERENCE;
            $this->putCallToPool($call);
        }
    }
    
    /**
     * Handle confbridge leave event
     * 
     * Case: someone leaves a conference. At this point we need to check
     *      how many members still left, and clean up conference if there
     *      is the only member.
     * 
     * @param array $params
     */ 
    public function onConfbridgeLeave($params)
    {
        $uid = $this->resolveCallUid($params['caller']);
        
        $this->trace(sprintf('ConfbridgeLeave: %s (%s), confID = %s, bridgeID = %s, UID = %s', 
            $params['caller']['number'], 
            $params['caller']['channel'],
            $params['conference'],
            $params['bridgeId'],
            $uid
        ));
        
        $members = $this->voipManager->getConferenceMembers($params['conference']);
        
        if (1 === count($members)) {
            $this->voipManager->hangup($members[0]['channel']);
        }
    }

    /**
     * Handle member pause event
     * 
     * Case: agent paused or unpaused.
     * 
     * @param array $params
     */ 
    public function onQueueMemberPause($params)
    {
        $this->trace(sprintf('QueueMemberPause: %s (%s), paused = %s', 
            $params['member'], 
            $params['queue'],
            $params['paused']
        ));

        $userId = $this->getUserBySip($params['member']);
        if ($userId !== 0) {
            broadcast(new MemberPause(
                $userId,
                $params['member'], 
                $params['queue'],
                $params['paused']
            ));
        }
    }

    /**
     * Handle member status event
     * 
     * Case: agent status update
     * 
     * @param array $params
     */ 
    public function onQueueMember($params)
    {
        $this->trace(sprintf('QueueMember: %s (%s), paused = %s', 
            $params['member'], 
            $params['queue'],
            $params['paused']
        ));

        $userId = $this->getUserBySip($params['member']);
        if ($userId !== 0) {
            broadcast(new MemberPause(
                $userId,
                $params['member'], 
                $params['queue'],
                $params['paused']
            ));
        }
    }
}