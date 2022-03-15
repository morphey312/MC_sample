<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class Services extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_service';
    
    /**
     * @var string
     */
    protected $destTable = 'services';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_service';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        if ($data->is_analyse == 1) {
            return false;
        }
        
        $result = $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'name_ua' => $this->toUTF('description_pay'),
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'payment_destination_id' => $this->fromRef('id_purpose_pay', 'list_purpose_pay', true),
            'disabled' => $this->toBool('not_used'),
            'is_base' => $this->toBool('is_base_service'),
        ]);
        
        if (empty($result['name_ua'])) {
            $result['name_ua'] = $result['name'];
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $existing = $this->getLocalConnection()
            ->table('services')
            ->where('name', '=', $data['name'])
            ->value('id');
        
        if ($existing !== null) {
            return $existing;
        }
        
        return parent::saveData($data, $row);
    }
    
    /**
     * @inherit
     */ 
    protected function preparePatchData($data)
    {
        $prepared = parent::preparePatchData($data);
        unset($prepared['name']);
        unset($prepared['name_ua']);
        return $prepared;
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_service_clinic', 'id_service', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                    'code' => $this->toUTF('code'),
                ], false, false);
            }
            $this->savePivotData('service_clinics', 'service_id', $localId, $clinicData);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('service_clinics', 'service_id', $localId);
        $this->saveRelatedData($remoteId, $localId, $data);
    }
}