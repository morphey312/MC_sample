<?php

namespace App\V1\Contracts\Services\Voip;

interface Message
{
    const UID_KEY = '_uid';
    const REPORT_MESSAGE    = 1;
    const SET_AVAILABLE     = 2;
    const SET_UNAVAILABLE   = 3;
    const HOLD_CALLER       = 4;
    const CREATE_BRIDGE     = 5;
    const JOIN_CALLER       = 6;
    const KICK_CALLER       = 7;
    const REQUEST_STATUS    = 8;
    
    /**
     * Get message type
     * 
     * @return string|int
     */ 
    public function getType();
    
    /**
     * Get message arguments
     * 
     * @return array
     */ 
    public function getArguments();
    
    /**
     * Get value of argument
     * 
     * @param string $name
     * @param mixed $default
     * 
     * @return mixed
     */ 
    public function getArgument($name, $default = null);
    
    /**
     * Serialize message as JSON
     * 
     * @return string
     */ 
    public function toJson();
    
    /**
     * Create message from JSON
     * 
     * @param string $json
     * 
     * @return Message
     */ 
    public static function fromJson($json);
    
    /**
     * Get message UID
     * 
     * @return string
     */ 
    public function getUID();
}