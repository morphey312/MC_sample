<?php

namespace App\V1\Console\Commands\MigrateData;

use Carbon\Carbon;

class PatientCards extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_patient_card';
    
    /**
     * @var string
     */
    protected $destTable = 'card_specializations';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_card_record';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $patientId = $this->getReference('list_patient', $data->id_patient);
        
        if (!$patientId) {
            return false;
        }
        
        $clinicId = $this->getReference('list_clinic', $data->id_clinic, true);
        if (!$clinicId) {
            return false;
        }
        
        $result = $this->pickData($data, [
            'specialization_id' => $this->fromRef('id_specialization', 'list_specialization', true),
            'created_at' => 'date_insert',
            'updated_at' => 'date_insert',
        ], false, false);
        
        $result['card_id'] = $this->getCardId($patientId, $clinicId);
        
        return $result;
    }
    
    /**
     * Get patient card ID
     * 
     * @param object $data
     * 
     * @return int
     */ 
    protected function getCardId($patientId, $clinicId)
    {
        $existing = $this->getLocalConnection()
            ->table('patient_cards')
            ->where('patient_id', $patientId)
            ->first();
        
        if ($existing !== null) {
            return $existing->id;
        }

        $maxCardNumber = (int) $this->getLocalConnection()
            ->table('patient_cards')
            ->max('number');
        
        return $this->getLocalConnection()
            ->table('patient_cards')
            ->insertGetId([
                'number' => $maxCardNumber + 1,
                'patient_id' => $patientId,
                'clinic_id' => $clinicId,
                'company_id' => $this->companyId,
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveData($data, $row)
    {
        $this->getLocalConnection()
            ->table('archive_card_numbers')
            ->insert([
                'card_id' => $data['card_id'],
                'specialization_id' => $data['specialization_id'],
                'number' => $row->id_card,
            ]);
            
        return parent::saveData($data, $row);
    }
}