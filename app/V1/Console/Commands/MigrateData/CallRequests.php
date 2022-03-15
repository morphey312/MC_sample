<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Employee;

class CallRequests extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'phone_request';
    
    /**
     * @var string
     */
    protected $destTable = 'call_requests';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_phone_request';
    
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
            'recall_from' => $this->toDate('date_wish1'),
            'recall_to' => $this->toDate('date_wish2'),
            'status' => $this->fromMap('status', [
                '1' => 'made',
                '2' => 'canceled',
                '3' => 'success_call',
            ], 'success_call'),
            'added' => ['is_auto', function($v) {
                return $v == 1 ? 'auto' : 'manual';
            }],
            'comment' => $this->toUTF('note'),
            'comment_canceled' => $this->toUTF('note_cancel'),
            'call_request_purpose_id' => $this->fromRef('id_phone_target', 'list_phone_target', true),
            'patient_id' => $this->fromRef('id_patient_real', 'list_patient'),
            'clinic_id' => $this->fromRef('id_clinic_real', 'list_clinic', true),
            'appointment_id' => $this->fromRef('id_record', 'RecordPatient'),
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'doctor_id' => $this->fromRef('id_doctor', 'list_personal'),
            'doctor_type' =>  ['id_doctor', function($v) {
                return Employee::RELATION_TYPE;
            }],
            'created_at' => 'date_insert',
            'updated_at' => 'date_insert',
        ], true, false);
        
        if (!empty($data->id_patient_real) && !$this->checkRequired($result, ['patient_id'])) {
            return false;
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->select(
                'phone_request.*', 
                'RecordPatient.id_patient AS id_patient_real', 
                'RecordPatient.id_person AS id_doctor', 
                'RecordPatient.id_specialization', 
                'list_personal.id_clinic AS id_clinic_real'
            )
            ->leftJoin('RecordPatient', 'RecordPatient.id_record', '=', 'phone_request.id_record')
            ->leftJoin('list_personal', 'list_personal.id_person', '=', 'RecordPatient.id_person');
            
        return $query;
    }
}