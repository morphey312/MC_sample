<?php

namespace App\V1\Console\Commands\MigrateData;

class Specializations extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_specialization';
    
    /**
     * @var string
     */
    protected $destTable = 'specializations';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_specialization';
    
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
            'genitive_name' => $this->toUTF('description_rod'),
            'status' => $this->toBool('not_used', true),
            'short_name' => $this->toUTF('short_description'),
            'course_days' => $this->toInt('cnt_work_day_dog'),
            'examination_days' => $this->toInt('cnt_work_day_not_dog'),
            'is_non_profile_patient' => $this->toBool('not_profile'),
            'not_use_for_new_patient_call' => $this->toBool('not_used'), 
        ]);
    }
    
    /**
     * @inherit
     */
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('adjacent_specializations', 'specialization_id', $localId);
        
        $adjacent = $this->getPivotData('Specialization_group', 'id_specialization_parent', $remoteId, function($query) {
            $query->whereNotIn('id_clinic', self::$excludeClinicIds);
            return $query;
        });
        
        if ($adjacent->count() !== 0) {
            $adjacentData = [];
            foreach ($adjacent as $spec) {
                $related = $this->getReference('list_specialization', $spec->id_specialization_child, true);
                if (!$related) {
                    continue;
                }
                $clinic = $this->getReference('list_clinic', $spec->id_clinic, true);
                if (!$clinic) {
                    continue;
                }
                $adjacentData[] = [
                    'clinic_id' => $clinic,
                    'adjacent_id' => $related,
                ];
            }
            $this->savePivotData('adjacent_specializations', 'specialization_id', $localId, $adjacentData);
        }
    }
}