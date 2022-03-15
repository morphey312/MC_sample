<?php

namespace App\V1\Console\Commands\MigrateData;

use DB;

trait FixtureTrait
{
    /**
     * @var array
     */
    protected $sources = [];
    
    /**
     * Get remote ID(s)
     * 
     * @param int|array $localId
     * @param string $table
     * 
     * @return int|array
     */ 
    protected function getRemoteId($localId, $table)
    {
        $query = $this->getLocalConnection()
            ->table('migration_refs')
            ->where('table', $table);
            
        if (is_array($localId)) {
            return $query
                ->whereIn('local_id', $localId)
                ->pluck('remote_id', 'local_id');
        } else {
            return $query
                ->where('local_id', $localId)
                ->value('remote_id');
        }
    }
    
    /**
     * Get local ID(s)
     * 
     * @param int|array $remoteId
     * @param string $table
     * 
     * @return int|array
     */ 
    protected function getLocalId($remoteId, $table)
    {
        $query = $this->getLocalConnection()
            ->table('migration_refs')
            ->where('table', $table);
            
        if (is_array($remoteId)) {
            return $query
                ->whereIn('remote_id', $remoteId)
                ->pluck('local_id', 'remote_id');
        } else {
            return $query
                ->where('remote_id', $remoteId)
                ->value('local_id');
        }
    }
    
    /**
     * @return \Illuminate\Database\Connection
     */ 
    protected function getRemoteConnection()
    {
        return DB::connection('sqlsrv');
    }
    
    /**
     * @return \Illuminate\Database\Connection
     */ 
    protected function getLocalConnection()
    {
        return DB::connection('mysql');
    }
    
    /**
     * Get/create migration source
     * 
     * @param string class
     * 
     * @return MigrateData\BaseMigrate
     */ 
    protected function getSource($class)
    {
        if (!isset($this->sources[$class])) {
            $src = new $class($this);
            $src->setup();
            return $this->sources[$class] = $src;
        }
        
        return $this->sources[$class];
    }
    
    /**
     * Convert string
     * 
     * @param string $str
     * @param bool $trim
     * 
     * @return string
     */ 
    protected function convertStr($str, $trim = true)
    {
        // return mb_convert_encoding($trim ? trim($str) : $str, 'utf8', 'cp1251');
        return $trim ? trim($str) : $str;
    }
}
