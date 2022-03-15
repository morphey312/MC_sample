<?php

namespace App\V1\Traits\Models;

trait HasCachedAttributes
{
    /**
     * @var array
     */
    protected $_cached = [];
    
    /**
     * Get cached attribute value, cache if not cached
     * 
     * @param string $attribute
     * @param bool $onlyCached
     * @param mixed $default
     * 
     * @return mixed
     */ 
    public function getCachedAttribute($attribute, $onlyCached = false, $default = null)
    {
        if (array_key_exists($attribute, $this->_cached)) {
            return $this->_cached[$attribute];
        }
        
        if ($onlyCached) {
            return $default;
        }
        
        return $this->forceCacheAttribute($attribute);
    }
    
    /**
     * Cache attribute value if not cached
     * 
     * @param string $attribute
     */ 
    public function cacheAttribute($attribute)
    {
        if (!array_key_exists($attribute, $this->_cached)) {
            $this->forceCacheAttribute($attribute);
        }
    }
    
    /**
     * Get value of attribute and put it into cache
     * 
     * @param string $attribute
     * 
     * @return mixed
     */ 
    public function forceCacheAttribute($attribute)
    {
        return $this->_cached[$attribute] = $this->getAttribute($attribute);
    }
}