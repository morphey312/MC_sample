<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

trait Parkinglots
{
    /**
     * @var array
     */
    protected $parkingLots;
    
    /**
     * Get parking lots info
     */ 
    protected function fetchParkingLots()
    {
        $this->parkingLots = $this->voipManager->getParkinglots();
    }
    
    /**
     * Check if the given extension belongs to parking lots
     * 
     * @param string|int $extension
     * 
     * @return bool
     */ 
    protected function isParkinglotExtension($extension)
    {
        foreach ($this->parkingLots as $lot) {
            if ($this->belongsToRange((int) $extension, (int) $lot['start'], (int) $lot['end'])) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if value matches the range
     * 
     * @param int $value
     * @param int $start
     * @param int $end
     */ 
    protected function belongsToRange($value, $start, $end)
    {
        return $value >= $start && $value <= $end;
    }
}