<?php

namespace App\V1\Services;

use Illuminate\Support\Facades\Redis;
use App\V1\Models\BaseModel;
use App\V1\Contracts\Services\MutexService as MutexServiceInterface;

class MutexService implements MutexServiceInterface
{
    const KEY_PREFIX = 'mutex:';

    /**
     * @inheritdoc
     */
    public function lock($resource, $expires = false)
    {
        $key = $this->getResourceKey($resource);

        if (Redis::exists($key)) {
            return false;
        }

        Redis::set($key, '1');

        if ($expires !== false) {
            Redis::expire($key, $expires);
        }

        return true;
    }

    /**
     * @inheritdoc
     */ 
    public function unlock($resource)
    {
        $key = $this->getResourceKey($resource);

        if (!Redis::exists($key)) {
            return false;
        }

        Redis::del([$key]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isLocked($resource)
    {
        $key = $this->getResourceKey($resource);

        return Redis::exists($key);
    }

    /**
     * Generate key for resource
     * 
     * @param mixed $resource
     * 
     * @return string
     */
    protected function getResourceKey($resource)
    {
        if (is_scalar($resource)) {
            return self::KEY_PREFIX . strval($resource);
        }

        if ($resource instanceof BaseModel) {
            return self::KEY_PREFIX . sprintf(
                '%s_%s',
                str_replace('\\', '_', 
                    str_replace('App\\V1\\Models\\', '', get_class($resource))),
                $resource->getKey()
            );
        }

        return self::KEY_PREFIX . get_class($resource);
    }
}
