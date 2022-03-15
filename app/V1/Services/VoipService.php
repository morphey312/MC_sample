<?php

namespace App\V1\Services;

use App\V1\Contracts\Services\VoipService as VoipServiceInterface;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Action\OriginateAction;
use PAMI\Message\Action\QueuePauseAction;
use PAMI\Message\Action\QueueUnpauseAction;
use PAMI\Message\Action\RedirectAction;
use PAMI\Message\Action\HangupAction;
use PAMI\Message\Action\QueueStatusAction;
use App\V1\Services\Voip\PAMI\Actions\ParkinglotsAction;
use App\V1\Services\Voip\PAMI\Actions\ParkAction;
use App\V1\Services\Voip\PAMI\Actions\ConfbridgeListRoomsAction;
use App\V1\Services\Voip\PAMI\Actions\ConfbridgeKickAction;
use App\V1\Services\Voip\PAMI\Actions\ConfbridgeListAction;
use PAMI\Client\Exception\ClientException;
use App\V1\Services\Voip\Exception\AmiException;
use App\V1\Services\Voip\Exception\DisconnectException;
use Illuminate\Support\Str;
use Exception;
use Closure;
use Log;

class VoipService implements VoipServiceInterface
{
    /**
     * @var bool
     */
    protected $connected = false;
    
    /**
     * @var ClientImpl
     */ 
    protected $client;
    
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var array
     */ 
    protected $listeners = [];
    
    /**
     * @var int
     */ 
    protected $numEvents = 0;
    
    /**
     * Service constructor
     */ 
    public function __construct()
    {
        $this->options = config('voip.options');
        $this->client = new ClientImpl(config('voip.client'));
        $this->client->registerEventListener(function($event) {
            $this->numEvents++;
            $this->processEvent($event);
        });
    }
    
    /**
     * Service destructor
     */ 
    function __destruct() 
    {
        $this->disconnect();
    }
    
    /**
     * @inheritdoc
     */ 
    public function connect()
    {
        if (!$this->connected) {
            $this->client->open();
            $this->connected = true;
        }
    }
    
    /**
     * @inheritdoc
     */ 
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        if ($this->connected) {
            $this->client->close();
            $this->connected = false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function onEvent($event, $callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Invalid callback');
        }
        $this->listeners[$event] = $callback;
    }
    
    /**
     * @inheritdoc
     */
    public function process()
    {
        $this->numEvents = 0;
        try {
            $this->client->process();
        } catch (ClientException $e) {
            if ($e->getMessage() === 'Error reading') {
                $this->disconnect();
                throw new DisconnectException('Asterisk server has gone');
            } else {
                throw new AmiException($e->getMessage());
            }
        }
        return $this->numEvents;
    }
    
    /**
     * Handle dynamic method calls
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (substr($method, 0, 2) === 'on') {
            return $this->onEvent(substr($method, 2), ...$parameters);
        }
        throw new Exception(
            sprintf('Call to undefined method %s::%s', static::class, $method)
        );
    }
    
    /**
     * Process incoming event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */ 
    protected function processEvent($event)
    {
        $name = $event->getName();
        switch ($name) {
            case self::EVENT_NEW_CHANNEL:
                return $this->processNewChannelEvent($event);
            
            case self::EVENT_NEW_CALLER_ID:
                return $this->processNewCallerIdEvent($event);
            
            case self::EVENT_QUEUE_CALLER_JOIN:
                return $this->processQueueCallerJoinEvent($event);
            
            case self::EVENT_QUEUE_CALLER_ABANDON:
                return $this->processQueueCallerAbandonEvent($event);
                
            case self::EVENT_AGENT_CONNECT:
                return $this->processAgentConnectEvent($event);
            
            case self::EVENT_BRIDGE_ENTER:
                return $this->processBridgeEnterEvent($event);
            
            case self::EVENT_BRIDGE_LEAVE:
                return $this->processBridgeLeaveEvent($event);
            
            case self::EVENT_HANGUP:
                return $this->processHangupEvent($event);
            
            case self::EVENT_PARKED_CALL:
                return $this->processParkedCallEvent($event);
            
            case self::EVENT_UNPARKED_CALL:
                return $this->processUnParkedCallEvent($event);
            
            case self::EVENT_PARKED_CALL_GIVEUP:
                return $this->processParkedCallGiveupEvent($event);
            
            case self::EVENT_CONFBRIDGE_JOIN:
                return $this->processConfbridgeJoinEvent($event);
            
            case self::EVENT_CONFBRIDGE_LEAVE:
                return $this->processConfbridgeLeaveEvent($event);
            
            case self::EVENT_MEMBER_PAUSE:
                return $this->processMemberPause($event);
            
            case self::EVENT_MEMBER_STATUS:
                return $this->processMemberStatus($event);
        }
        return false;
    }
    
    /**
     * Process NewChannel event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processNewChannelEvent($event)
    {
        return $this->delegateEvent(self::EVENT_NEW_CHANNEL, $this->getSourceDetails($event));
    }
    
    /**
     * Process NewCallerid event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processNewCallerIdEvent($event)
    {
        return $this->delegateEvent(self::EVENT_NEW_CALLER_ID, $this->getSourceDetails($event));
    }
    
    /**
     * Process QueueCallerJoin event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processQueueCallerJoinEvent($event)
    {
        return $this->delegateEvent(self::EVENT_QUEUE_CALLER_JOIN, [
            'caller' => $this->getSourceDetails($event),
            'queue' => $event->getKey('Queue'),
        ]);
    }
    
    /**
     * Process QueueCallerAbandon event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processQueueCallerAbandonEvent($event)
    {
        return $this->delegateEvent(self::EVENT_QUEUE_CALLER_ABANDON, [
            'caller' => $this->getSourceDetails($event),
            'queue' => $event->getKey('Queue'),
        ]);
    }
    
    /**
     * Process AgentConnect event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processAgentConnectEvent($event)
    {
        return $this->delegateEvent(self::EVENT_AGENT_CONNECT, [
            'caller' => $this->getSourceDetails($event),
            'agent' => $this->getDestinationDetails($event),
            'interface' => $event->getKey('Interface'),
            'agentNumber' => $this->getSipNumberByInterface($event->getKey('Interface')),
        ]);
    }
    
    /**
     * Process BridgeEnter event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processBridgeEnterEvent($event)
    {
        return $this->delegateEvent(self::EVENT_BRIDGE_ENTER, [
            'caller' => $this->getSourceDetails($event),
            'bridgeId' => $event->getKey('BridgeUniqueid'),
            'technology' => $event->getKey('BridgeTechnology'),
        ]);
    }
    
    /**
     * Process BridgeLeave event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processBridgeLeaveEvent($event)
    {
        return $this->delegateEvent(self::EVENT_BRIDGE_LEAVE, [
            'caller' => $this->getSourceDetails($event),
            'bridgeId' => $event->getKey('BridgeUniqueid'),
            'technology' => $event->getKey('BridgeTechnology'),
        ]);
    }
    
    /**
     * Process Hangup event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processHangupEvent($event)
    {
        return $this->delegateEvent(self::EVENT_HANGUP, [
            'caller' => $this->getSourceDetails($event),
            'reason' => $event->getKey('Cause-txt'),
        ]);
    }
    
    /**
     * Process ParkedCall event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processParkedCallEvent($event)
    {
        return $this->delegateEvent(self::EVENT_PARKED_CALL, [
            'parkee' => $this->getParkeeDetails($event),
            'lot' => $event->getKey('Parkinglot'),
            'space' => $event->getKey('ParkingSpace'),
        ]);
    }
    
    /**
     * Process UnParkedCall event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processUnParkedCallEvent($event)
    {
        return $this->delegateEvent(self::EVENT_UNPARKED_CALL, [
            'parkee' => $this->getParkeeDetails($event),
            'retriever' => $this->getRetrieverDetails($event),
            'lot' => $event->getKey('Parkinglot'),
            'space' => $event->getKey('ParkingSpace'),
        ]);
    }
    
    /**
     * Process ParkedCallGiveUp event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processParkedCallGiveupEvent($event)
    {
        return $this->delegateEvent(self::EVENT_PARKED_CALL_GIVEUP, [
            'parkee' => $this->getParkeeDetails($event),
            'lot' => $event->getKey('Parkinglot'),
            'space' => $event->getKey('ParkingSpace'),
        ]);
    }
    
    /**
     * Process ConfbridgeJoin event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processConfbridgeJoinEvent($event)
    {
        return $this->delegateEvent(self::EVENT_CONFBRIDGE_JOIN, [
            'caller' => $this->getSourceDetails($event),
            'bridgeId' => $event->getKey('BridgeUniqueid'),
            'conference' => $event->getKey('Conference'),
        ]);
    }
    
    /**
     * Process ConfbridgeLeave event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processConfbridgeLeaveEvent($event)
    {
        return $this->delegateEvent(self::EVENT_CONFBRIDGE_LEAVE, [
            'caller' => $this->getSourceDetails($event),
            'bridgeId' => $event->getKey('BridgeUniqueid'),
            'conference' => $event->getKey('Conference'),
        ]);
    }

    /**
     * Process QueueMemberPause event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processMemberPause($event)
    {
        return $this->delegateEvent(self::EVENT_MEMBER_PAUSE, [
            'member' => $this->getSipNumberByInterface($event->getKey('Interface')),
            'queue' => $event->getKey('Queue'),
            'paused' => $event->getKey('Paused'),
        ]);
    }

    /**
     * Process QueueMember event
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return bool
     */
    protected function processMemberStatus($event)
    {
        return $this->delegateEvent(self::EVENT_MEMBER_STATUS, [
            'member' => $this->getSipNumberByInterface($event->getKey('StateInterface')),
            'queue' => $event->getKey('Queue'),
            'paused' => $event->getKey('Paused'),
        ]);
    }
    
    /**
     * Get dial source details
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return array
     */ 
    protected function getSourceDetails($event)
    {
        return $this->getDialerDetails($event);
    }
    
    /**
     * Get dial destination details
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return array
     */ 
    protected function getDestinationDetails($event)
    {
        return $this->getDialerDetails($event, 'Dest');
    }
    
    /**
     * Get parked call details
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return array
     */ 
    protected function getParkeeDetails($event)
    {
        return $this->getDialerDetails($event, 'Parkee');
    }
    
    /**
     * Get retriever details
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * 
     * @return array
     */ 
    protected function getRetrieverDetails($event)
    {
        return $this->getDialerDetails($event, 'Retriever');
    }
    
    /**
     * Get dialer details
     * 
     * @param \PAMI\Message\Event\EventMessage $event
     * @param string $prefix
     * 
     * @return array
     */ 
    protected function getDialerDetails($event, $prefix = '')
    {
        return [
            'number' => $event->getKey($prefix . 'CallerIDNum'),
            'name' => $event->getKey($prefix . 'CallerIDName'),
            'channel' => $event->getKey($prefix . 'Channel'),
            'channelState' => (int) $event->getKey($prefix . 'ChannelState'),
            'channelStateDesc' => $event->getKey($prefix . 'ChannelStateDesc'),
            'context' => $event->getKey($prefix . 'Context'),
            'source' => $this->detectSource($event->getKey($prefix . 'Context')),
            'extension' => $event->getKey($prefix . 'Exten'),
            'connectedNumber' => $event->getKey($prefix . 'ConnectedLineNum'),
            'connectedName' => $event->getKey($prefix . 'ConnectedLineName'),
            'uid' => $event->getKey($prefix . 'Uniqueid'),
            'linkedId' => $event->getKey($prefix . 'Linkedid'),
        ];
    }
    
    /**
     * Handover event to the listener if any
     * 
     * @param string $name
     * @param array $params
     * 
     * @return bool
     */ 
    protected function delegateEvent($name, $params)
    {
        if (array_key_exists($name, $this->listeners)) {
            $result = call_user_func($this->listeners[$name], $params);
            return is_bool($result) ? $result : true;
        }
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function startCall($from, $to, array $options = [])
    {
        $opts = $this->getCallOptions($options);
        $action = new OriginateAction($this->getChannel($from));
        $action->setContext($opts['context']);
        $action->setPriority($opts['priority']);
        $action->setTimeout($opts['timeout']);
        $action->setExtension($to);
        $action->setCallerId($from);
        $action->setAsync($opts['async']);
        $action->setActionID($opts['actionId']);
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
        
        return $response;
    }
    
    /**
     * @inheritdoc
     */
    public function setAvailable($agent, $available = true, array $options = [])
    {
        $interface = $this->getInterface($agent);
        
        if ($available) {
            $opts = $this->getUnpauseOptions($options);
            $action = new QueueUnpauseAction($interface, $opts['queue']);
        } else {
            $opts = $this->getPauseOptions($options);
            $action = new QueuePauseAction($interface, $opts['queue'], $opts['reason']);
        }
        
        $action->setActionID($opts['actionId']);
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
        
        return $response;
    }
    
    /**
     * Get list of parking lots
     * 
     * @return array
     */ 
    public function getParkinglots()
    {
        $list = [];
        
        $action = new ParkinglotsAction();
        $action->setActionID($this->generateActionId());
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
        
        foreach ($response->getEvents() as $event) {
            if ($event->getName() == self::EVENT_PARKINGLOT) {
                $list[] = [
                    'name' => $event->getKey('Name'),
                    'start' => $event->getKey('StartSpace'),
                    'end' => $event->getKey('StopSpace'),
                ];
            } elseif ($event->getName() == self::EVENT_PARKINGLOTS_COMPLETE) {
                break;
            }
        }
        
        return $list;
    }
    
    /**
     * @inheritdoc
     */
    public function parkChannel($channel, array $options = [])
    {
        $opts = $this->getParkOptions($options);
        
        $action = new ParkAction($channel, $opts['lot']);
        $action->setActionID($opts['actionId']);
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
    }
    
    /**
     * @inheritdoc
     */ 
    public function startConference($channel1, $channel2, array $options = [])
    {
        $slot = $this->getConferenceSlot();
        
        if (!$slot) {
            throw new AmiException('Unable to get free conference slot');
        }
        
        $opts = $this->getConferenceOptions($options);
        $action = new RedirectAction($channel1, $slot, $opts['context'], $opts['priority']);
        $action->setExtraChannel($channel2);
        $action->setExtraExtension($slot);
        $action->setExtraContext($opts['context']);
        $action->setExtraPriority($opts['priority']);
        $action->setActionID($opts['actionId']);
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
        
        return $slot;
    }
    
    /**
     * @inheritdoc
     */ 
    public function joinConference($channel, $conference, array $options = [])
    {
        $opts = $this->getConferenceOptions($options);
        $action = new RedirectAction($channel, $conference, $opts['context'], $opts['priority']);
        $action->setActionID($opts['actionId']);
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
    }
    
    /**
     * @inheritdoc
     */ 
    public function hangup($channel)
    {
        $action = new HangupAction($channel);
        $action->setActionID($this->generateActionId());
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }
    }
    
    /**
     * @inheritdoc
     */ 
    public function getConferenceMembers($conference)
    {
        $action = new ConfbridgeListAction($conference);
        $action->setActionID($this->generateActionId());
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            return [];
        }
        
        $list = [];
        
        foreach ($response->getEvents() as $event) {
            if ($event->getName() == self::EVENT_CONFBRIDGE_LIST) {
                $list[] = $this->getSourceDetails($event);
            } elseif ($event->getName() == self::EVENT_CONFBRIDGE_LIST_COMPLETE) {
                break;
            }
        }
        
        return $list;
    }

    /**
     * @inheritdoc
     */ 
    public function requestMemberStatus($sip)
    {
        $action = new QueueStatusAction('', $this->getInterface($sip));
        $action->setActionID($this->generateActionId());
        $response = $this->client->send($action);
        
        if (!$response->isSuccess()) {
            throw new AmiException($response->getKey('Message'));
        }

        foreach ($response->getEvents() as $event) {
            if ($event->getName() == self::EVENT_MEMBER_STATUS) {
                $this->processMemberStatus($event);
            }
        }
    }
    
    /**
     * Get default options for originate action
     * 
     * @param array $defaults
     * 
     * @return array
     */ 
    protected function getCallOptions(array $defaults)
    {
        return array_merge([
            'context' => $this->options['context'],
            'priority' => '1',
            'timeout' => 30000,
            'async' => true,
            'actionId' => $this->generateActionId(),
        ], $defaults);
    }
    
    /**
     * Get default options for unpause action
     * 
     * @param array $defaults
     * 
     * @return array
     */ 
    protected function getUnpauseOptions(array $defaults)
    {
        return array_merge([
            'queue' => false,
            'actionId' => $this->generateActionId(),
        ], $defaults);
    }
    
    /**
     * Get default options for pause action
     * 
     * @param array $defaults
     * 
     * @return array
     */ 
    protected function getPauseOptions(array $defaults)
    {
        return array_merge([
            'queue' => false,
            'reason' => false,
            'actionId' => $this->generateActionId(),
        ], $defaults);
    }
    
    /**
     * Get channel for call
     * 
     * @param string $agent
     * 
     * @return string
     */ 
    protected function getChannel($agent)
    {
        $channel = $this->options['driver'] . '/' . $agent;
        if ($this->options['route']) {
            $channel .= $this->options['route'];
        }
        return $channel;
    }
    
    /**
     * Get interface for queue member
     * 
     * @param string $agent
     * 
     * @return string
     */ 
    protected function getInterface($agent)
    {
        return $this->options['driver'] . '/' . $agent;
    }
    
    /**
     * Generate unique action ID
     * 
     * @return string
     */ 
    protected function generateActionId()
    {
        return uniqid('', true);
    }
    
    /**
     * Detect caller source
     * 
     * @param string $context
     * 
     * @return string
     */ 
    protected function detectSource($context)
    {
        foreach ($this->options['sources'] as $src => $patterns) {
            foreach (explode(',', $patterns) as $pattern) {
                if (strpos($pattern, '*') !== false) {
                    if (fnmatch($pattern, $context)) {
                        return $src;
                    }
                } else {
                    if ($context === $pattern) {
                        return $src;
                    }
                }
            }
        }
    }
    
    /**
     * Get SIP number from interface
     * 
     * @param string $interface
     * 
     * @return string
     */ 
    protected function getSipNumberByInterface($interface)
    {
        if (Str::startsWith($interface, $this->options['driver'] . '/')) {
            return Str::after($interface, $this->options['driver'] . '/');
        }
        return null;
    }
    
    /**
     * Get free slot for a conference
     * 
     * @return string
     */ 
    protected function getConferenceSlot()
    {
        $action = new ConfbridgeListRoomsAction();
        $action->setActionID($this->generateActionId());
        $response = $this->client->send($action);
        
        if (!$response->isSuccess() && 
            !Str::contains($response->getKey('Message'), 'No active conferences')) {
            return null;
        }
        
        $used = [];
        foreach ($response->getEvents() as $event) {
            if ($event->getName() == self::EVENT_CONFBRIDGE_LIST_ROOMS) {
                $used[] = $event->getKey('Conference');
            } elseif ($event->getName() == self::EVENT_CONFBRIDGE_LIST_ROOMS_COMPLETE) {
                break;
            }
        }
        
        foreach ($this->getConferenceSlotRanges() as $range) {
            $start = $range['start'];
            $end = $range['end'];
            for ($number = $start; $number <= $end; $number++) {
                $slot = sprintf($range['format'], $number);
                if (!in_array($slot, $used)) {
                    return $slot;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get slot ranges for conference
     * 
     * @return array
     */ 
    protected function getConferenceSlotRanges()
    {
        return $this->options['conference']['slots'];
    }
    
    /**
     * Get options for new conference
     * 
     * @param array $defaults
     * 
     * @return array
     */ 
    protected function getConferenceOptions(array $defaults)
    {
        return array_merge([
            'context' => $this->options['conference']['context'],
            'priority' => 1,
            'actionId' => $this->generateActionId(),
        ], $defaults);
    }
    
    /**
     * Get options for park
     * 
     * @param array $defaults
     * 
     * @return array
     */ 
    protected function getParkOptions(array $defaults)
    {
        return array_merge([
            'lot' => $this->options['parkinglot']['name'],
            'actionId' => $this->generateActionId(),
        ], $defaults);
    }
}