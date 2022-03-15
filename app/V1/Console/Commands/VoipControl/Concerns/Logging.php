<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

trait Logging
{
    /**
     * @var string
     */
    protected $logChannel = 'asterisk';
    
    /**
     * Write debugging information to the log file
     * 
     * @param string $message
     */ 
    protected function trace($message)
    {
        Log::channel($this->logChannel)->info($message);
    }
    
    /**
     * Write error report to the log file
     * 
     * @param string $message
     */ 
    protected function logError($message)
    {
        Log::channel($this->logChannel)->error($message);
    }
}