<?php

namespace App\V1\Console\Commands\MigrateData;

class CallResults extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_phone_result';
    
    /**
     * @var string
     */
    protected $destTable = 'call_results';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_phone_result';
    
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
            'name' => $this->toUTF('description'),
            'use_for_new_calls' => $this->toBool('not_used', true),
            'use_for_statistics' => $this->toBool('is_for_statistic'),
            'use_for_is_first_patient' => $this->toBool('is_group1'),
            'use_for_repeated_patient' => $this->toBool('is_group2'),
            'use_for_unspecialized_patient' => $this->toBool('is_group3'),
            'use_for_not_patient' => $this->toBool('is_group4'),
        ]);
    }
}