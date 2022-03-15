<?php

namespace App\V1\Console\Commands\MigrateData;

class InformationSources extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_source_information';
    
    /**
     * @var string
     */
    protected $destTable = 'information_sources';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_source_information';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @var array
     */ 
    protected $allClinicIds = null;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'is_active' => $this->toBool('not_used', true),
            'is_collective_form' => $this->toBool('type_form_site'),
            'media_type' => $this->fromRef('id_TypeReklama', 'list_TypeReklama', true),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        if ($data->is_all_clinic == 1) {
            $clinicData = [];
            foreach ($this->getAllClinicIds() as $clinicId) {
                $clinicData[] = [
                    'clinic_id' => $clinicId,
                ];
            }
            $this->savePivotData('information_source_clinics', 'source_id', $localId, $clinicData);
        } else {
            $clinics = $this->getPivotData('list_source_information_clinic', 'id_source_information', $remoteId, function($query) {
                return $query;
            });
            
            if ($clinics->count() !== 0) {
                $clinicData = [];
                foreach ($clinics as $clinic) {
                    $clinicData[] = [
                        'clinic_id' => $this->getReference('list_clinic', $clinic->id_clinic, true),
                    ];
                }
                $this->savePivotData('information_source_clinics', 'source_id', $localId, $clinicData);
            }
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('information_source_clinics', 'source_id', $localId);
        $this->saveRelatedData($remoteId, $localId, $data);
    }
    
    /**
     * Get all clinics ids
     * 
     * @return array
     */ 
    protected function getAllClinicIds()
    {
        if ($this->allClinicIds === null) {
            $this->allClinicIds = $this->getLocalConnection()
                ->table('clinics')
                ->where('created_by_id', self::$createdById)
                ->pluck('id');
        }
        
        return $this->allClinicIds;
    }
}