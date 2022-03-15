<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

use Illuminate\Support\Facades\Redis;
use App\V1\Models\Call\CallLog;

trait CallsPool
{
    /**
     * Put call to the pool
     * 
     * @param \App\V1\Models\Call\CallLog $call
     */ 
    protected function putCallToPool($call, $persist = true)
    {
        if ($persist || !$call->exists) {
            $this->callLogs->persist($call);
        }
        
        $key = $this->redisPrefixCalls . $call->uid;
        Redis::set($key, json_encode($call->getAttributes()));
        Redis::expire($key, self::CALLS_POOL_TTL);
    }
    
    /**
     * Get call from the pool
     * 
     * @param string $uid
     * 
     * @return \App\V1\Models\Call\CallLog
     */ 
    protected function getCallFromPool($uid)
    {
        $key = $this->redisPrefixCalls . $uid;
        
        if (Redis::exists($key)) {
            $data = Redis::get($key);
            $call = new CallLog();
            $call->loadFromArray(json_decode($data, true));
            return $call;
        }
        
        return $this->callLogs->findByUid($uid);
    }
    
    /**
     * Remove call from the pool
     * 
     * @param string $uid
     */ 
    protected function removeCallFromPool($uid)
    {
        $key = $this->redisPrefixCalls . $uid;
        Redis::del([$key]);
    }
}