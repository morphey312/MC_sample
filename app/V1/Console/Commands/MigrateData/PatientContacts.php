<?php

namespace App\V1\Console\Commands\MigrateData;

class PatientContacts extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_patient';
    
    /**
     * @var string
     */
    protected $destTable = 'patient_contacts';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_patient';
    
    /**
     * @inherit
     */ 
    protected function getProgressKey()
    {
        return sprintf('data_migration_%s_contacts', $this->srcTable);
    }
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $patientId = $this->getReference('list_patient', $data->id_patient);
        
        if (!$patientId) {
            return false;
        }
        
        $contactsData = [];
        
        if ($data->phone) {
            $phoneInfo = $this->parsePhone(trim($data->phone));
            
            if ($phoneInfo !== null) {
                $contactsData[] = [
                    'type' => 'phone',
                    'primary' => 1,
                    'value' => $phoneInfo[0],
                    'comment' => $phoneInfo[1],
                    'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone1', true),
                    'patient_id' => $patientId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ];
            }
        }
        
        if ($data->phone2) {
            $phoneInfo = $this->parsePhone(trim($data->phone2));
            
            if ($phoneInfo !== null) {
                $contactsData[] = [
                    'type' => 'phone',
                    'primary' => 0,
                    'value' => $phoneInfo[0],
                    'comment' => $phoneInfo[1],
                    'clinic_id' => $this->getReference('list_clinic', 'id_clinic_phone2', true),
                    'patient_id' => $patientId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                ];
            }
        }
        
        //~ if (strpos($data->email, '@') !== false) {
            //~ $contactsData[] = [
                //~ 'type' => 'email',
                //~ 'primary' => 0,
                //~ 'value' => $this->convertStr($data->email),
                //~ 'comment' => null,
                //~ 'clinic_id' => null,
                //~ 'patient_id' => $patientId,
                //~ 'created_at' => $this->timestamp(),
                //~ 'updated_at' => $this->timestamp(),
            //~ ];
        //~ }
        
        if (count($contactsData) === 0) {
            return false;
        }
        
        return $contactsData;
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $this->getLocalConnection()
                ->table($this->destTable)
                ->insert($data);
        
        return false;
    }
    
    /**
     * Parse phone to have correct format
     * 
     * @param string $phone
     * 
     * @return array
     */ 
    protected function parsePhone($phone)
    {
        if (preg_match('#([0-9]{7,10})($|[^0-9].*$)#u', $phone, $matches)) {
            return [
                $matches[1],
                $this->convertStr($matches[2]),
            ];
        }
        return null;
    }
}