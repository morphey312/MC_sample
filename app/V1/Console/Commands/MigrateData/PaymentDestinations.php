<?php

namespace App\V1\Console\Commands\MigrateData;

class PaymentDestinations extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_purpose_pay';
    
    /**
     * @var string
     */
    protected $destTable = 'service_payment_destinations';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_purpose_pay';
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'color' => ['prihod_color', function($v) {
                return sprintf('#%06x', $v);
            }],
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_purpose_pay_clinic', 'id_purpose_pay', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                ], false, false);
            }
            $this->savePivotData('clinic_payment_destination', 'payment_destination_id', $localId, $clinicData);
        }
    }
}