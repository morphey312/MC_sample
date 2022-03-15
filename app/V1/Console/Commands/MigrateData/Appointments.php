<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;
use App\V1\Models\Workspace;

class Appointments extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'RecordPatient';
    
    /**
     * @var string
     */
    protected $destTable = 'appointments';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_record';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $result = $this->pickData($data, [
            'comment' => $this->toUTF('note'),
            'end' => $this->toTime('datetime_end'),
            'start' => $this->toTime('datetime_begin'),
            'date' => $this->toDate('date_'),
            'is_first' => $this->toBool('is_primary_patient'),
            'operator_id' => $this->fromRef('id_operator', 'list_personal'),
            'creator_id' => $this->fromRef('id_operator', 'list_personal'),
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'discount_card_id' => $this->fromRef('id_bonus_card', 'list_patient_bonus_card'),
            'treatment_course_id' => $this->fromRef('id_dog', 'RecordPatientDog'),
            'card_specialization_id' => $this->fromRef('id_card_specialization', 'list_specialization', true),
            'doctor_id' => $this->fromRef('id_person', 'list_personal'),
            'doctor_type' => $this->fromMap('is_human', [
                '1' => Employee::RELATION_TYPE,
                '0' => Workspace::RELATION_TYPE,
            ], Employee::RELATION_TYPE),
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'source_id' => $this->fromRef('id_source_information', 'list_source_information'),
            'appointment_status_id' => $this->fromRef('status', 'list_status', true),
            'status_reason_id' => $this->fromRef('id_reason_status', 'list_status_reason', true),
            'status_reason_comment' => $this->toUTF('note_status'),
            'appointment_delete_reason_id' => $this->fromRef('id_reason_delete_record', 'list_reason_delete_record', true),
            'delete_reason_comment' => $this->toUTF('note_reason_delete_record'),
            'is_deleted' => $this->toBool('is_deleted'),
            'rejection_reason' => $this->fromRef('rejection_reason', 'list_reason_not_taking_treatment', true),
            'created_at' => 'date_insert',
            'updated_at' => 'date_insert',
        ], true, false);
        
        if (!$this->checkRequired($result, ['patient_id', 'operator_id', 'doctor_id'])) {
            return false;
        }
        
        if (empty($result['clinic_id'])) {
            $result['clinic_id'] = $this->getLocalConnection()
                ->table('patient_clinics')
                ->where('patient_id', $result['patient_id'])
                ->value('clinic_id');
        }
        
        if (!$this->checkRequired($result, ['clinic_id'])) {
            return false;
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'RecordPatient.*', 
                'list_patient_card.id_specialization AS id_card_specialization',
                'list_personal.id_clinic',
                'list_personal.is_human',
                'RecordPatientDog.id_reason_not_taking_treatment AS rejection_reason',
            ]))
            ->leftJoin('list_patient_card', 'list_patient_card.id_card_record', '=', 'RecordPatient.id_card_record')
            ->leftJoin('list_personal', 'list_personal.id_person', '=', 'RecordPatient.id_person')
            ->leftJoin('RecordPatientDog', 'RecordPatientDog.id_dog', '=', 'RecordPatient.id_dog');
            
        return $query;
    }
}