<?php

namespace App\V1\Console\Commands\VoipControl\Concerns;

trait QueueMapping
{
    /**
     * @var array
     */
    protected $queueClinics;
    
    /**
     * Get clinic related to the given queue
     * 
     * @param string $queue
     * 
     * @return int
     */ 
    protected function getQueueClinic($queue)
    {
        return $this->queueClinics[$queue] ?? null;
    }
    
    /**
     * Fetch queue-to-clinic mapping
     */ 
    protected function fetchClinicQueues()
    {
        $this->queueClinics = $this->clinics->getQueues();
    }
}