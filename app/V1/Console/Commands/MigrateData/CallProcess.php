<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Patient;
use App\V1\Models\Employee;

class CallProcess extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'phone_operator_action';
    
    /**
     * @var string
     */
    protected $destTable = 'call_process_logs';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id';
    
    /**
     * @var bool
     */ 
    protected $mapRefs = false;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $result = $this->pickData($data, [
            'unprocessibility_reason' => $this->fromRef('ReasonNotPossibleProcessing', 'list_reason_not_possible_processing', true),
            'unprocessibility_reason_comment' => $this->toUTF('note'),
            'comment' => $this->toUTF('note'),
            'is_patient' => $this->toBool('is_patient'),
            'is_first_visit' => $this->toBool('is_primary'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'operator_id' => $this->fromRef('id_operator', 'list_personal'),
            'sip_number' => 'sip',
            'started_at' => 'date_begin',
            'processed_at' => 'date_end',
        ]);
        
        if (!$this->checkRequired($result, ['operator_id'])) {
            return false;
        }
        
        if ($data->ReasonNotPossibleProcessing) {
            $result['status'] = 'improcessible';
            $result['comment'] = null;
        } elseif ($data->is_completed != 1) {
            $result['status'] = 'nonprocessed';
            $result['unprocessibility_reason_comment'] = null;
        } else {
            $result['status'] = 'processed';
            $result['unprocessibility_reason_comment'] = null;
        }
        
        if ($data->incoming_call_id) {
            $result['source'] = 'call';
            $result['call_id'] = $this->createIncomingCall($data, $result);
        } elseif ($data->outgoing_call_id) {
            $result['source'] = 'outgoing_call';
            $result['call_id'] = $this->createOutgoingCall($data, $result);
        }
        
        if ($data->enqiry_id) {
            $result['source'] = 'site_enquiry';
            $result['enquiry_id'] = $this->createSiteEnquiry($data, $result);
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'phone_operator_action.*',
                '(select top 1 id_person from list_personal where list_personal.sip = phone_operator_action.sip) AS id_operator',
                '(select top 1 id_clinic from list_personal where list_personal.sip = phone_operator_action.sip) AS id_clinic',
                'phone_operator_in.id AS incoming_call_id',
                'phone_operator_in.phone_number AS incoming_call_phone_number',
                'phone_operator_in.date_begin AS incoming_call_started',
                'phone_operator_in.date_end_real AS incoming_call_ended',
                'phone_operator_out.id AS outgoing_call_id',
                'phone_operator_out.phone_number AS outgoing_call_phone_number',
                'phone_operator_out.date_begin AS outgoing_call_started',
                'phone_operator_out.date_end_real AS outgoing_call_ended',
                'phone_operator_out.id_patient',
                'phone_operator_form_site.id AS enqiry_id',
                'phone_operator_form_site.type_form_site AS enqiry_type',
                'CAST(phone_operator_form_site.name_site AS TEXT) AS enquirer_name',
                'phone_operator_form_site.phone_site AS enquirer_phone_number',
                'phone_operator_form_site.email_site AS enquirer_email',
                'CAST(phone_operator_form_site.question_site AS TEXT) AS enqiry_note',
                'phone_operator_form_site.specialization_site AS enqiry_specialization',
                'phone_operator_form_site.doctor_site AS enqiry_doctor',
                'phone_operator_form_site.card_site AS enquirer_card_number',
                'phone_operator_form_site.datetime_record_site AS enqiry_appointment_time',
            ]))
            ->leftJoin('phone_operator_in', 'phone_operator_in.id_action', '=', 'phone_operator_action.id')
            ->leftJoin('phone_operator_out', 'phone_operator_out.id_action', '=', 'phone_operator_action.id')
            ->leftJoin('phone_operator_form_site', 'phone_operator_form_site.id_action', '=', 'phone_operator_action.id');
            
        return $query;
    }
    
    /**
     * Create incoming call
     * 
     * @param object $data
     * @param array $processData
     * 
     * @return int
     */ 
    protected function createIncomingCall($data, $processData)
    {
        if (!preg_match('#^[0-9]{7-10}$#', $data->incoming_call_phone_number)) {
            return null;
        } 
        
        return $this->getLocalConnection()
            ->table('call_logs')
            ->insertGetId($this->pickData($data, [
                'phone_number' => 'incoming_call_phone_number',
                'started_at' => 'incoming_call_started',
                'ended_at' => 'incoming_call_ended',
            ], false, false) + [
                'type' => 'incoming',
                'source' => 'call',
                'status' => 'ended',
                'callee_id' => $processData['operator_id'],
                'callee_type' => $processData['operator_id'] ? Employee::RELATION_TYPE : null,
                'clinic_id' => $processData['clinic_id'],
            ]);
    }
    
    /**
     * Create outgoing call
     * 
     * @param object $data
     * @param array $processData
     * 
     * @return int
     */ 
    protected function createOutgoingCall($data, $processData)
    {
        $patientId = $this->getReference('list_patient', $data->id_patient);
        
        return $this->getLocalConnection()
            ->table('call_logs')
            ->insertGetId($this->pickData($data, [
                'phone_number' => 'outgoing_call_phone_number',
                'started_at' => 'outgoing_call_started',
                'ended_at' => 'outgoing_call_ended',
            ], false, false) + [
                'type' => 'outgoing',
                'source' => 'call',
                'status' => 'ended',
                'caller_id' => $processData['operator_id'],
                'caller_type' => $processData['operator_id'] ? Employee::RELATION_TYPE : null,
                'callee_id' => $patientId,
                'callee_type' => $patientId ? Patient::RELATION_TYPE : null,
                'patient_id' => $patientId,
                'clinic_id' => $processData['clinic_id'],
            ]);
    }
    
    /**
     * Create enquiry
     * 
     * @param object $data
     * @param array $processData
     * 
     * @return int
     */ 
    protected function createSiteEnquiry($data, $processData)
    {
        return $this->getLocalConnection()
            ->table('site_enquiries')
            ->insertGetId($this->pickData($data, [
                'category' => $this->fromMap('enqiry_type', [
                    '1' => 'appointment',
                    '2' => 'question',
                    '3' => 'price',
                ], 'question'),
                'name' => ['enquirer_name', function($v) {
                    return mb_substr($this->convertStr($v, true), 0, 200);
                }],
                'email' => ['enquirer_email', function($v) {
                    return mb_substr($this->convertStr($v, true), 0, 200);
                }],
                'phone_number' => ['enquirer_phone_number', function($v) {
                    return mb_substr($this->convertStr($v, true), 0, 200);
                }],
                'card_number' => ['enquirer_card_number', function($v) {
                    return mb_substr($this->convertStr($v, true), 0, 20);
                }],
                'notes' => $this->toUTF('enqiry_note'),
                'date' => 'enqiry_appointment_time',
            ]) + [
                'status' => 'processed',
                'clinic_id' => $processData['clinic_id'],
                'operator_id' => $processData['operator_id'],
            ]);
    }
}