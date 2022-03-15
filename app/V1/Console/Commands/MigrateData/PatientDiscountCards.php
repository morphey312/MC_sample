<?php

namespace App\V1\Console\Commands\MigrateData;

use Carbon\Carbon;

class PatientDiscountCards extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_patient_bonus_card';
    
    /**
     * @var string
     */
    protected $destTable = 'issued_discount_cards';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_bonus_card';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        if (!$this->getReference('list_patient', $data->id_patient)) {
            return false;
        }
        
        $result = $this->pickData($data, [
            'discount_card_type_id' => $this->fromRef('id_type_bonus_card', 'list_type_bonus_card', true),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'number' => ['card_number', function($v) {
                if (preg_match('#[-]([0-9]+)[-]#', $v, $match)) {
                    return $match[1];
                }
                if (preg_match('#([0-9]+)#', $v, $match)) {
                    return $match[1];
                }
                return '1';
            }],
            'issued' => $this->toDate('date_out'),
            'valid_from' => $this->toDate('date_begin'),
            'expires' => $this->toDate('date_end'),
            'comment' => $this->toUTF('note'),
        ]);
        
        if (empty($result['valid_from'])) {
            $result['valid_from'] = $result['issued'];
        }
        
        if (empty($result['expires'])) {
            $result['expires'] = '2050-01-01';
        }
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $this->getLocalConnection()
            ->table('patient_issued_cards')
            ->insert([
                'patient_id' => $this->getReference('list_patient', $data->id_patient),
                'issued_card_id' => $localId,
                'disabled' => $data->not_used == 1 ? 1 : 0,
                'is_owner' => 1,
            ]);
    }
    
    /**
     * @inherit
     */ 
    protected function addRecordsFilter($query)
    {
        $query->whereNotIn('list_patient_bonus_card.id_clinic', self::$excludeClinicIds);
        
        return $query;
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $patientId = $this->getReference('list_patient', $data->id_patient);
        
        $this->getLocalConnection()
            ->table('patient_issued_cards')
            ->where('patient_id', $patientId)
            ->where('issued_card_id', $localId)
            ->delete();
        
        $this->saveRelatedData($remoteId, $localId, $data);
    }
}