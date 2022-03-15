<?php

namespace App\V1\Console\Commands\MigrateData;

class AdjacentSpecializations extends BasePivotMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'Specialization_group';
    
    /**
     * @var string
     */
    protected $destTable = 'adjacent_specializations';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_specialization_parent';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'specialization_id' => $this->fromRef('id_specialization_parent', 'list_specialization', true),
            'adjacent_id' => $this->fromRef('id_specialization_child', 'list_specialization', true),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
        ], false, false);
    }
    
    /**
     * @inherit
     */ 
    protected function addRecordsFilter($query)
    {
        $query->whereNotIn('id_clinic', self::$excludeClinicIds);
        
        return $query;
    }
}