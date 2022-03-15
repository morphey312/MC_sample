<?php

namespace App\V1\Console\Commands\MigrateData;

class Cabinets extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_cabinet';
    
    /**
     * @var string
     */
    protected $destTable = 'workspaces';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_cabinet';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
        ]);
    }
}