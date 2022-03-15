<?php

namespace App\V1\Console\Commands\MigrateData;

class BasePivotMigrate extends BaseMigrate
{
    /**
     * Get next chunk of data
     * 
     * @param int|bool $from
     * @param int $limit
     * 
     * @return array
     */ 
    protected function getNextChunk($from, $limit)
    {
        $query = $this->getRemoteConnection()
            ->table($this->srcTable);
        
        if ($from !== false) {
            $query->where($this->remoteKey, '>', $from);
        }
        
        $query = $this->addRecordsFilter($query);
        $minKey = $query->min($this->remoteKey);
        
        $query = $this->getRemoteConnection()
            ->table($this->srcTable)
            ->where($this->remoteKey, $minKey);
        
        $query = $this->customizeQuery($query);
        $query = $this->addRecordsFilter($query);
        
        return $query->get();
    }
}