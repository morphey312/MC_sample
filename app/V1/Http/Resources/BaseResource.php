<?php

namespace App\V1\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Closure;

class BaseResource extends JsonResource
{
    /**
     * Convert date to string
     * 
     * @param Carbon $date
     * @param bool $withTime
     * 
     * @return string
     */ 
    protected function convertDate($date, $withTime = true, $format = null)
    {
        if ($date === null) {
            return null;
        }
        
        if (! $date instanceof Carbon) {
            $date = Carbon::parse($date); 
        }
        $dateFormat = $format ? $format : ($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
        return $date->format($dateFormat);
    }

    /**
     * Get values from relation
     * 
     * @param string $relation
     * @param Closure $callback
     * @param bool $forceLoad
     * @param mixed $default
     * 
     * @return mixed
     */ 
    protected function getRelationValues($relation, Closure $callback, $forceLoad = false, $default = null) 
    {
        if (! $this->resource->relationLoaded($relation)) {
            if (!$forceLoad) {
                return $default;
            }
        }
        
        if ($this->resource->{$relation} === null) {
            return $default;
        }
        
        return $callback($this->resource->{$relation});
    }
    
    /**
     * Get values from the object
     * 
     * @param string $attribute
     * @param Closure $callback
     * @param mixed $default
     * 
     * @return mixed
     */ 
    protected function getObjectValues($attribute, Closure $callback, $default = null) 
    {
        $data = object_get($this->resource, $attribute, null);
        
        if (null === $data) {
            return $default;
        }
        
        return $callback($data);
    }
    
    /**
     * Get cached attribute value
     * 
     * @param string $attribute
     * @param Closure $callback
     * @param mixed $default
     * @param bool $onlyCached
     * 
     * @return mixed
     */ 
    protected function getCachedObjectValues($attribute, Closure $callback, $onlyCached = true, $default = null) 
    {
        $data = $this->resource->getCachedAttribute($attribute, $onlyCached);
        
        if (null === $data) {
            return $default;
        }
        
        return $callback($data);
    }
}