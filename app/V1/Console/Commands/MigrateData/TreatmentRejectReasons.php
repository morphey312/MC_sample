<?php

namespace App\V1\Console\Commands\MigrateData;

class TreatmentRejectReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_reason_not_taking_treatment';
    
    /**
     * @var string
     */
    protected $destTable = 'handbooks';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_reason';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'value' => $this->toUTF('description'),
        ]) + [
            'category' => 'reason_refusing_treatment',
        ];
    }
}