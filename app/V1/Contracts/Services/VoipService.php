<?php

namespace App\V1\Contracts\Services;

use Closure;

interface VoipService
{
    const CHANNEL_ALL = 'all';
    
    const SRC_INCOMING_CALL = 'incomingcall';
    const SRC_OUTGOING_CALL = 'outgoingcall';
    const SRC_CALLBACK = 'callback';
    const SRC_SITE_CALLBACK = 'sitecallback';
    const SRC_UNKNOWN = 'unknown';
    
    const EVENT_NEW_CHANNEL = 'Newchannel';
    const EVENT_NEW_CALLER_ID = 'NewCallerid';
    const EVENT_QUEUE_CALLER_JOIN = 'QueueCallerJoin';
    const EVENT_QUEUE_CALLER_ABANDON = 'QueueCallerAbandon';
    const EVENT_AGENT_CONNECT = 'AgentConnect';
    const EVENT_BRIDGE_ENTER = 'BridgeEnter';
    const EVENT_BRIDGE_LEAVE = 'BridgeLeave';
    const EVENT_HANGUP = 'Hangup';
    const EVENT_PARKINGLOT = 'Parkinglot';
    const EVENT_PARKINGLOTS_COMPLETE = 'ParkinglotsComplete';
    const EVENT_PARKED_CALL = 'ParkedCall';
    const EVENT_UNPARKED_CALL = 'UnParkedCall';
    const EVENT_CONFBRIDGE_LIST = 'ConfbridgeList';
    const EVENT_CONFBRIDGE_LIST_COMPLETE = 'ConfbridgeListComplete';
    const EVENT_CONFBRIDGE_LIST_ROOMS = 'ConfbridgeListRooms';
    const EVENT_CONFBRIDGE_LIST_ROOMS_COMPLETE = 'ConfbridgeListRoomsComplete';
    const EVENT_CONFBRIDGE_JOIN = 'ConfbridgeJoin';
    const EVENT_CONFBRIDGE_LEAVE = 'ConfbridgeLeave';
    const EVENT_PARKED_CALL_GIVEUP = 'ParkedCallGiveUp';
    const EVENT_MEMBER_PAUSE = 'QueueMemberPause';
    const EVENT_MEMBER_STATUS = 'QueueMember';
    
    /**
     * Connect to the server
     */ 
    public function connect();
    
    /**
     * Disconnect from the server
     */ 
    public function disconnect();
    
    /**
     * Do communication with AMI server and process messages
     * 
     * @return int number of processed messages
     */
    public function process();
    
    /**
     * Add callback to certain event
     * 
     * @param string $event
     * @param mixed $callback
     */
    public function onEvent($event, $callback);
    
    /**
     * Start call between two extensions
     * 
     * @param string $from
     * @param string $to
     * @param array $options
     * 
     * @return mixed
     */ 
    public function startCall($from, $to, array $options = []);
    
    /**
     * Change availability status of the extension
     * 
     * @param string $extension
     * @param bool $available
     * @param array $options
     */
    public function setAvailable($extension, $available = true, array $options = []);
    
    /**
     * Park the channel
     * 
     * @param string $channel
     * @param array $options
     * 
     * @return string
     */
    public function parkChannel($channel, array $options = []);
    
    /**
     * Start a conference
     * 
     * @param string $channel1
     * @param string $channel2
     * @param array $options
     * 
     * @return string
     */ 
    public function startConference($channel1, $channel2, array $options = []);
    
    /**
     * Join channel to the conference
     * 
     * @param string $channel
     * @param string $conference
     * @param array $options
     */ 
    public function joinConference($channel, $conference, array $options = []);
    
    /**
     * Hangup a channel
     * 
     * @param string $channel
     */ 
    public function hangup($channel);
    
    /**
     * Get list of parking lots
     * 
     * @return array
     */ 
    public function getParkinglots();
    
    /**
     * Get list of members in the conference
     * 
     * @param string $conference
     * 
     * @return array
     */ 
    public function getConferenceMembers($conference);

    /**
     * Request queue member status
     * 
     * @param string $sip
     */
    public function requestMemberStatus($sip);
}