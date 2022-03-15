<?php

namespace App\V1\Console\Commands\MigrateData;

class ClinicSpecializations extends BasePivotMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_specialization_clinic';
    
    /**
     * @var string
     */
    protected $destTable = 'clinic_specialization';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_specialization';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'status' => ['id_specialization', function($v) {
                return 1;
            }],
        ], false, true);
    }
}