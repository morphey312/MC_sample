<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

use Illuminate\Support\Facades\Redis;

trait SipMapping
{
    /**
     * Get user ID related to the given sip number
     * 
     * @param string $queue
     * 
     * @return int
     */ 
    protected function getUserBySip($sip)
    {
        $key = self::SIP_USER_PREFIX . $sip;
        
        if (Redis::exists($key)) {
            return (int) Redis::get($key);
        }

        $userId = $this->employeeClinics->getUserIdBySipNumber($sip);
        Redis::set($key, strval($userId));
        Redis::expire($key, self::SIP_MAP_TTL);

        return $userId;
    }
}