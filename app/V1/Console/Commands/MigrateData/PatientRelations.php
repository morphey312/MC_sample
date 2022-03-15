<?php

namespace App\V1\Console\Commands\MigrateData;

class PatientRelations extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_patient_relation';
    
    /**
     * @var string
     */
    protected $destTable = 'patient_relatives';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $result = $this->pickData($data, [
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'relative_id' => $this->fromRef('id_patient_link', 'list_patient'),
            'relation' => $this->fromMap('type_link', [
                '1' => 'grandmother',
                '2' => 'brother',
                '3' => 'grandson',
                '4' => 'granddaughter',
                '5' => 'grandfather',
                '6' => 'daughter',
                '7' => 'uncle',
                '8' => 'wife',
                '9' => 'mother',
                '10' => 'husband',
                '11' => 'father',
                '12' => 'nephew',
                '13' => 'niece',
                '14' => 'sister',
                '15' => 'son',
                '16' => 'aunt',
            ]),
        ], false, false);
        
        if (!$this->checkRequired($result, ['patient_id', 'relative_id'])) {
            return false;
        }
        
        return $result;
    }
}