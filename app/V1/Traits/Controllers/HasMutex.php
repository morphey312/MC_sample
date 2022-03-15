<?php

namespace App\V1\Traits\Controllers;

use Exception;
use App\V1\Facades\Mutex;
use Closure;

trait HasMutex 
{
    /**
     * Do something with locked resource
     *
     * @param mixed $resource
     * @param Closure $callback
     * @param bool|int $lockExpires
     * 
     * @return mixed
     */
    public function locking($resource, Closure $callback, $lockExpires = false)
    {
        if (!Mutex::lock($resource, $lockExpires)) {
            return $this->respondError('Resource locked', [], 409);
        }

        try {
            $result = $callback($resource);
            Mutex::unlock($resource);
            return $result;
        } catch (Exception $e) {
            Mutex::unlock($resource);
            throw $e;
        }
    }
}