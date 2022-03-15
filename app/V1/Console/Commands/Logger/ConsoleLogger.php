<?php

namespace App\V1\Console\Commands\Logger;

use Psr\Log\AbstractLogger;
use Illuminate\Console\Command;

class ConsoleLogger extends AbstractLogger
{
    /**
     * @var Command
     */ 
    protected $command;
    
    /**
     * Constructor
     * 
     * @param Command $command
     */ 
    public function __construct(Command $command)
    {
        $this->command = $command;
    }
    
    /**
     * @inheritdoc
     */ 
    public function log($level, $message, array $context = array())
    {
        $this->command->line("{$level}: {$message}\n");
    }
}