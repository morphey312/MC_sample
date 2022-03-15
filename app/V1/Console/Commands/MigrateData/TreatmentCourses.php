<?php

namespace App\V1\Console\Commands\MigrateData;

use Carbon\Carbon;

class TreatmentCourses extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'RecordPatientDog';
    
    /**
     * @var string
     */
    protected $destTable = 'treatment_courses';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_dog';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $dateStart = 'date1';
        $dateEnd = 'date2';
        
        if (empty($data->date1)) {
            $dateStart = 'date_insert';
            $dateEnd = 'date_insert';
        }
        
        $result = $this->pickData($data, [
            'start' => $this->toDate($dateStart),
            'end' => $this->toDate($dateEnd),
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'doctor_id' => $this->fromRef('id_doctor', 'list_patient'),
            'card_specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
        ]);
        
        if (!$this->checkRequired($result, ['patient_id'])) {
            return false;
        }
        
        if ($data->is_closed != 1 && 
            $result['end'] !== null && 
            Carbon::now()->lessThan($result['end'])) {
            $result['end'] = null;
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'RecordPatientDog.*', 
                'list_patient_card.id_specialization',
            ]))
            ->leftJoin('list_patient_card', 'list_patient_card.id_card_record', '=', 'RecordPatientDog.id_card_record');
            
        return $query;
    }
}