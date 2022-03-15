<?php

namespace App\V1\Services\Voip;

use App\V1\Contracts\Services\Voip\MessageQueue as MessageQueueInterface;
use App\V1\Contracts\Services\Voip\Message as MessageInterface;
use Illuminate\Support\Facades\Redis;

class MessageQueue implements MessageQueueInterface
{
    const QUEUE_NAME = 'voip_messages';
    const REPORT_PREFIX = 'report_';
    const DEFAULT_TTL = 30;
    const DEFAULT_TIMEOUT = 30;
    const CHECK_INTERVAL = 100000;
    
    /**
     * @inheritdoc
     */ 
    public function push(MessageInterface $message)
    {
        Redis::rpush(self::QUEUE_NAME, $message->toJson());
    }
    
    /**
     * @inheritdoc
     */ 
    public function pop()
    {
        if (Redis::llen(self::QUEUE_NAME) > 0) {
            $data = Redis::lpop(self::QUEUE_NAME);
            return Message::fromJson($data);
        }
        return null;
    }
    
    /**
     * @inheritdoc
     */ 
    public function reportBack(MessageInterface $message, $ttl = null)
    {
        $key = self::REPORT_PREFIX . $message->getUID();
        Redis::set($key, $message->toJson());
        Redis::expire($key, $ttl === null ? self::DEFAULT_TTL : $ttl);
    }
    
    /**
     * @inheritdoc
     */ 
    public function waitReport($uid, $timeout = null)
    {
        $key = self::REPORT_PREFIX . $uid;
        $timeLeft = ($timeout === null ? self::DEFAULT_TIMEOUT : $timeout) * 1000000;
        while (true) {
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                Redis::del([$key]);
                return Message::fromJson($data);
            }
            if ($timeLeft > 0) {
                $timeLeft -= self::CHECK_INTERVAL;
                usleep(self::CHECK_INTERVAL);
            } else {
                break;
            }
        }
        return null;
    }
}