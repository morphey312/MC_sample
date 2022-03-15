<?php

namespace App\V1\Console\Commands\MigrateData;

class MediaTypes extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_TypeReklama';
    
    /**
     * @var string
     */
    protected $destTable = 'handbooks';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_TypeReklama';
    
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
            'category' => 'media_type',
        ];
    }
}