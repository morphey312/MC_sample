<?php

namespace App\V1\Console\Commands\MigrateData;

class DiscountCardNumerations extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_bonus_card_numeration';
    
    /**
     * @var string
     */
    protected $destTable = 'card_numbering_kinds';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_numeration';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'unique' => $this->toBool('is_unique_number'),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_bonus_card_numeration_clinic', 'id_numeration', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                    'numbering_type' => $this->fromMap('type_numeration', [
                        '0' => 'card_numbering_common',
                        '1' => 'card_numbering_clinic',
                    ], 'card_numbering_clinic'),
                    'start_number' => $this->toInt('first_number'),
                    'prefix' => $this->toUTF('prefix'),
                    'suffix' => $this->toUTF('suffix'),
                ], false, false);
            }
            $this->savePivotData('card_numbering_clinic', 'number_kind_id', $localId, $clinicData);
        }
    }
}