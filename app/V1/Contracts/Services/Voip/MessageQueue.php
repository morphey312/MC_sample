<?php

namespace App\V1\Contracts\Services\Voip;

interface MessageQueue
{
    /**
     * Push message to the queue
     * 
     * Message $message
     */ 
    public function push(Message $message);
    
    /**
     * Pop message from the queue
     * 
     * @return Message
     */ 
    public function pop();
    
    /**
     * Report back
     * 
     * @param Message $message
     * @param int $ttl
     */ 
    public function reportBack(Message $message, $ttl = null);
    
    /**
     * Wait report
     * 
     * @param string $uid
     * @param int $timeout
     * 
     * @return Message
     */ 
    public function waitReport($uid, $timeout = null);
}