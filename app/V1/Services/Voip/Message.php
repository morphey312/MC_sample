<?php

namespace App\V1\Services\Voip;

use App\V1\Contracts\Services\Voip\Message as MessageInterface;
use Exception;

class Message implements MessageInterface
{
    /**
     * @var string
     */ 
    protected $type;
    
    /**
     * @var array
     */ 
    protected $arguments;
    
    /**
     * Message constructor
     * 
     * @param string|int $type
     * @param array $arguments
     */
    public function __construct($type, array $arguments = [])
    {
        $this->type = $type;
        $this->arguments = $arguments;
        if (!array_key_exists(self::UID_KEY, $this->arguments)) {
            $this->arguments[self::UID_KEY] = $this->generateUID();
        }
    }
    
    /**
     * @inheritdoc
     */ 
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @inheritdoc
     */ 
    public function getArguments()
    {
        return $this->arguments;
    }
    
    /**
     * @inheritdoc
     */ 
    public function getArgument($name, $default = null)
    {
        return $this->arguments[$name] ?? $default;
    }
    
    /**
     * @inheritdoc
     */ 
    public function toJson() 
    {
        return json_encode([$this->type, $this->arguments]);
    }
    
    /**
     * @inheritdoc
     */ 
    public static function fromJson($json) 
    {
        $data = @json_decode($json, true);
        $type = array_shift($data);
        $arguments = array_shift($data);
        
        if (is_scalar($type) && is_array($arguments)) {
            return new static($type, $arguments);
        }
        
        throw new Exception('Malformed message');
    }
    
    /**
     * @inheritdoc
     */ 
    public function getUID()
    {
        return $this->arguments[self::UID_KEY] ?? null;
    }
    
    /**
     * Generate unique ID
     * 
     * @return string
     */ 
    protected function generateUID()
    {
        return uniqid('', true);
    }
}