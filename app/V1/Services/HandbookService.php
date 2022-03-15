<?php

namespace App\V1\Services;

use App\V1\Contracts\Repositories\HandbookRepository;
use App\V1\Contracts\Services\HandbookService as HandbookServiceInterface;
use App\V1\Contracts\Multilingual;
use App\V1\Facades\Client;
use Cache;
use Auth;

class HandbookService implements HandbookServiceInterface
{
    const CACHE_KEY = 'handbook';
    
    /**
     * @var HandbookRepository
     */ 
    protected $handbooks;
    
    /**
     * @var array
     */ 
    protected $options;
    
    /**
     * Constructor
     * 
     * @param HandbookRepository $handbooks
     */ 
    public function __construct(HandbookRepository $handbooks)
    {
        $this->handbooks = $handbooks;
        $this->loadOptions();
    }

    /**
     * @inherit
     */ 
    public function get($key = null)
    {
        $user = Auth::user();
        $lkey = '_';

        if ($user instanceof Multilingual) {
            $lkey = $user->getLocaleSuffix() ?: $lkey;
        }

        if ($key === null) {
            return array_map(function($category) use($lkey) {
                return array_map(function($str) use($lkey) {
                    return array_get($str, $lkey) ?: array_get($str, '_');
                }, $category);
            }, $this->options);
        }

        return array_get($this->options, "$key.$lkey") ?: array_get($this->options, "$key._");
    }

    /**
     * @inherit
     */
    public function toEhealthKey($category, $key)
    {
        return array_get($this->options, "$category.$key.ehealth");
    }
    
    /**
     * @inherit
     */ 
    public function toArray()
    {
        return $this->options;
    }
    
    /**
     * Get options keys from particular category
     * 
     * @param string $category
     * 
     * @return array
     */ 
    public function keys($category)
    {
        return array_keys(array_get($this->options, $category, []));
    }
    
    /**
     * Load options
     */ 
    protected function loadOptions($force = false)
    {
        if ($this->options === null || $force) {
            $this->options = Cache::rememberForever($this->getCacheKey(), function() {
                $options = [];
                foreach ($this->handbooks->get() as $record) {
                    $options[$record->category][$record->key] = [
                        '_' => $record->value,
                        'lc1' => $record->value_lc1,
                        'lc2' => $record->value_lc2,
                        'lc3' => $record->value_lc3,
                        'ehealth' => $record->key_ehealth,
                    ];
                }
                return $options;
            });
        }
    }

    /**
     * Get cache key for particular case
     * 
     * @return string
     */
    protected function getCacheKey()
    {
        return self::CACHE_KEY . '_' . Client::getClientId();
    }
    
    /**
     * Remove handbook from the cache
     */ 
    public function flushCache()
    {
        Cache::forget($this->getCacheKey());
    }
}
