<?php

namespace App\V1\Console\Commands\MigrateData;

class NotProcessedCallReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_reason_not_possible_processing';
    
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
            'category' => 'reason_impossibility_of_call_processing',
        ];
    }
}