<?php

namespace App\V1\Console\Commands\MigrateData;

class PaymentTypes extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_type_pay';
    
    /**
     * @var string
     */
    protected $destTable = 'payment_methods';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_type_pay';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
        ]);
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $existing = $this->getLocalConnection()
                ->table($this->destTable)
                ->where('name', $data['name'])
                ->first();
        
        if ($existing !== null) {
            return $existing->id;
        }
        
        return $this->getLocalConnection()
            ->table($this->destTable)
            ->insertGetId($data);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinicData = [];
        
        $clinicData[] = $this->pickData($data, [
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'is_fiscal' => $this->toBool('is_fiscal'), 
        ], false, false);
        
        $this->savePivotData('clinic_payment_method', 'payment_method_id', $localId, $clinicData);
    }
}