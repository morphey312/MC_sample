<?php

namespace App\V1\Console\Commands\MigrateData;

class Calls extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'Phone';
    
    /**
     * @var string
     */
    protected $destTable = 'calls';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_phone';
    
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
            'time' => $this->toTime('datetime_phone'),
            'date' => $this->toDate('datetime_phone'),
            'call_result_id' => $this->fromRef('id_phone_result', 'list_phone_result', true),
            'call_delete_reason_id' => $this->fromRef('id_reason_delete_phone', 'list_reason_delete_phone', true),
            'delete_reason_comment' => $this->toUTF('note_reason_delete_phone'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'employee_id' => $this->fromRef('id_operator', 'list_personal'),
            'contact_id' => $this->fromRef('id_patient', 'list_patient'),
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'call_request_id' => $this->fromRef('id_phone_request', 'phone_request'),
            'is_first' => $this->toBool('is_primary'),
            'created_at' => 'date_insert',
            'updated_at' => 'date_insert',
        ], true, false);
        
        if (!$this->checkRequired($result, ['contact_id', 'employee_id'])) {
            return false;
        }
        
        $result['contact_type'] = 'patient';
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'Phone.*',
                'phone_request.id_phone_request',
            ]))
            ->leftJoin('phone_request', 'Phone.id_phone', '=', 'phone_request.id_phone');
            
        return $query;
    }
}