<?php

namespace App\V1\Console\Commands\MigrateData;

class AppointmentStatusReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_status_reason';
    
    /**
     * @var string
     */
    protected $destTable = 'status_reasons';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_reason';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveData($data, $row)
    {
        $existing = $this->getLocalConnection()
            ->table($this->destTable)
            ->where('name', $data['name'])
            ->first();
        
        if ($existing !== null) {
            return $existing->id;
        }
              
        return $this->getLocalConnection()
            ->table($this->destTable)
            ->insertGetId($data);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $related = [$this->pickData($data, [
            'appointment_status_id' => $this->fromRef('id_status', 'list_status', true),
            'default' => $this->toBool('default_'),
        ], false, false)];
        
        $this->savePivotData('appointment_status_reason', 'status_reason_id', $localId, $related);
    }
}