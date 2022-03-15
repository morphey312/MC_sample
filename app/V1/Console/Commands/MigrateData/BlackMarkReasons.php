<?php

namespace App\V1\Console\Commands\MigrateData;

class BlackMarkReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_reason_set_black_label';
    
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
            'category' => 'black_mark_reason',
        ];
    }
}