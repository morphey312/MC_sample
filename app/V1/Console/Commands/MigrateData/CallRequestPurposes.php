<?php

namespace App\V1\Console\Commands\MigrateData;

class CallRequestPurposes extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_phone_target';
    
    /**
     * @var string
     */
    protected $destTable = 'call_request_purposes';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_phone_target';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'auto_add' => $this->toBool('is_auto'),
            'manual_add' => $this->toBool('is_default'),
        ]);
    }
}