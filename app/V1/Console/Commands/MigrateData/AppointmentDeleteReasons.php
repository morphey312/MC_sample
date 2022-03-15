<?php

namespace App\V1\Console\Commands\MigrateData;

class AppointmentDeleteReasons extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_reason_delete_record';
    
    /**
     * @var string
     */
    protected $destTable = 'appointment_delete_reasons';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_reason_delete_record';
    
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
            'not_use_for_appointment_delete' => $this->toBool('not_used'),
            'comment_required' => $this->toBool('need_note'),
        ]);
    }
}