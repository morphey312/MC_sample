<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;

class EmployeeClinics extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_personal';
    
    /**
     * @var string
     */
    protected $destTable = 'employee_clinics';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_person';
    
    /**
     * @var bool
     */ 
    protected $mapRefs = false;
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function getProgressKey()
    {
        return sprintf('data_migration_%s_clinics', $this->srcTable);
    }
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        if ($data->is_human == 1) {
            $this->destTable = 'employee_clinics';

            return $this->pickData($data, [
                'employee_id' => $this->fromRef('id_person', 'list_personal'),
                'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                'status' => $this->fromMap('status', [
                    '0' => 'working',
                    '1' => 'not_working',
                    '2' => 'not_working',
                    '3' => 'probation',
                    '4' => 'removed',
                ], 'working'),
                'position_id' => $this->fromRef('id_type', 'list_type', true),
                'sip_number' => 'sip',
            ], false, true);
        } else {
            $this->destTable = 'workspace_clinics';
            
            return $this->pickData($data, [
                'workspace_id' => $this->fromRef('id_person', 'list_personal'),
                'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                'appointment_duration' => 'duration_min',
                'sip_number' => 'sip',
            ], false, true);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        if ($data->is_human == 1) {
            $this->saveHumanRelatedData($remoteId, $localId, $data);
        } else {
            $this->saveWorkspaceRelatedData($remoteId, $localId, $data);
        }
    }
    
    /**
     * Save related data for human
     * 
     * @param int $remoteId
     * @param int $localId
     * @param object $data
     */ 
    protected function saveHumanRelatedData($remoteId, $localId, $data)
    {
        $specsializations = $this->getPivotData('list_person_specialization', 'id_person', $remoteId);
        
        if ($specsializations->count() !== 0) {
            $specsializationData = [];
            foreach ($specsializations as $specsialization) {
                $specsializationData[] = [
                    'specialization_id' => $this->getReference('list_specialization', $specsialization->id_specialization, true),
                    'priority' => $specsialization->order_ == 1 ? 1 : 0,
                ];
            }
            
            $this->savePivotData('employee_specialization', 'employee_clinic_id', $localId, $specsializationData);
            
            if ($data->duration_min > 0) {
                $doctorData = [
                    'appointment_duration' => $data->duration_min,
                    'appointment_duration_repeated' => $data->duration_min,
                ];
            
                $this->savePivotData('doctors', 'employee_clinic_id', $localId, [$doctorData]);
            }
        }
    }

    /**
     * Save related data for workspace
     * 
     * @param int $remoteId
     * @param int $localId
     * @param object $data
     */ 
    protected function saveWorkspaceRelatedData($remoteId, $localId, $data)
    {
        $specsializations = $this->getPivotData('list_person_specialization', 'id_person', $remoteId);
        
        if ($specsializations->count() !== 0) {
            $specsializationData = [];
            foreach ($specsializations as $specsialization) {
                $specsializationData[] = [
                    'specialization_id' => $this->getReference('list_specialization', $specsialization->id_specialization, true),
                ];
            }
            
            $this->savePivotData('workspace_specialization', 'workspace_clinic_id', $localId, $specsializationData);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function getLocalIdToPatch($data)
    {
        if ($data->is_human == 1) {
            $employeeId = $this->getReference('list_personal', $data->id_person);
            $clinicId = $this->getReference('list_clinic', $data->id_clinic, true);
            
            return $this->getLocalConnection()
                ->table('employee_clinics')
                ->where('employee_id', $employeeId)
                ->where('clinic_id', $clinicId)
                ->value('id');
        } else {
            $workspaceId = $this->getReference('list_personal', $data->id_person);
            $clinicId = $this->getReference('list_clinic', $data->id_clinic, true);
            
            return $this->getLocalConnection()
                ->table('workspace_clinics')
                ->where('workspace_id', $workspaceId)
                ->where('clinic_id', $clinicId)
                ->value('id');
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        if ($data->is_human == 1) {
            $this->clearPivotData('employee_specialization', 'employee_clinic_id', $localId);
            $this->clearPivotData('doctors', 'employee_clinic_id', $localId);
            $this->saveHumanRelatedData($remoteId, $localId, $data);
        } else {
            $this->clearPivotData('workspace_specialization', 'workspace_clinic_id', $localId);
            $this->saveWorkspaceRelatedData($remoteId, $localId, $data);
        }
    }
}