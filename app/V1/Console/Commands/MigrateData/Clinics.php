<?php

namespace App\V1\Console\Commands\MigrateData;

class Clinics extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_clinic';
    
    /**
     * @var string
     */
    protected $destTable = 'clinics';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_clinic';
    
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
            'official_name' => $this->toUTF('description'),
            'map_link' => 'link_clinic_addr',
            'address' => ['clinic_addr', function($v) {
                return [
                    'address' => $this->convertStr($v),
                ];
            }],
            'status' => ['id_state', function($v) {
                return 1;
            }],
            'currency' => ['id_state', function($v) {
                return 'eur';
            }],
        ]);
    }
    
    /**
     * @inherit
     */
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('clinic_specialization', 'clinic_id', $localId);
        
        $specializations = $this->getPivotData('list_specialization_clinic', 'id_clinic', $remoteId);
        
        if ($specializations->count() !== 0) {
            $specializationData = [];
            foreach ($specializations as $specialization) {
                $specializationData[] = $this->pickData($specialization, [
                    'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
                    'first_patient_appointment_limit' => $this->toInt('cnt_min_record'),
                    'status' => $this->toBool('is_not_active', true),
                    'days_since_last_visit' => 'last_visit_day_interval',
                    'show_days_since_message' => $this->toBool('show_days_since_message'),
                ], false, true);
            }
            $this->savePivotData('clinic_specialization', 'clinic_id', $localId, $specializationData);
        }
    }
}