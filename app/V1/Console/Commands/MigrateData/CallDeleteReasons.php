<?php

namespace App\V1\Console\Commands\MigrateData;

class CallDeleteReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_reason_delete_phone';
    
    /**
     * @var string
     */
    protected $destTable = 'call_delete_reasons';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_reason_delete_phone';
    
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
            'include_to_report' => $this->toBool('is_included_in_report'),
            'use_for_delete' => $this->toBool('not_used', true),
            'comment_required' => $this->toBool('need_note'),
        ]);
    }
}