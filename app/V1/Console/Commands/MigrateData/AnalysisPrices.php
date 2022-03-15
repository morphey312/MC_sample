<?php

namespace App\V1\Console\Commands\MigrateData;

use Carbon\Carbon;
use App\V1\Models\Analysis;

class AnalysisPrices extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_service_analyse_tarif';
    
    /**
     * @var string
     */
    protected $destTable = 'prices';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_tarif';
    
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
            'service_id' => $this->fromRef('id_analyse', 'list_service_analyse'),
            'date_from' => $this->toDate('date1'),
            'date_to' => ['date2', function($v) {
                return $v == '2099-12-31 00:00:00' ? null : Carbon::parse($v)->format('Y-m-d');
            }],
            'cost' => 'price',
            'self_cost' => 'cost',
            'currency' => $this->fromMap('id_valuta', [
                '4' => 'eur',
            ], 'eur'),
            'set_id' => ['id_valuta', function($v) {
                return $this->basePriceSetId;
            }],
        ], false, true);
        
        if (empty($result['service_id'])) {
            return false;
        }
        
        $result['service_type'] = Analysis::RELATION_TYPE;
        
        return $result;
    }
    
    /**
     * @inherit
     */
    protected function saveData($data, $row)
    {
        $clinicsIds = $this->getPivotData('list_service_analyse_tarif_clinic', 'id_tarif', $row->id_tarif, function($query) {
            return $query;
        })->map(function($clinic) {
            return $this->getReference('list_clinic', $clinic->id_clinic, true);
        })->all();
        
        if (count($clinicsIds) !== 0) {
            $existing = $this->getLocalConnection()
                ->table('prices')
                ->where('service_id', '=', $data['service_id'])
                ->where('service_type', '=', $data['service_type'])
                ->where('date_from', '=', $data['date_from'])
                ->where('set_id', '=', $data['set_id'])
                ->whereExists(function ($query) use($clinicsIds) {
                    $query->from('price_clinics')
                          ->whereRaw('price_clinics.price_id = prices.id')
                          ->whereIn('price_clinics.clinic_id', $clinicsIds);
                })
                ->value('id');
            
            if ($existing !== null) {
                if ($this->patchData($existing, $data, $row)) {
                    $this->patchRelatedData($row->id_tarif, $existing, $row);
                }
                return false;
            }
        }
            
        return parent::saveData($data, $row);
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_service_analyse_tarif_clinic', 'id_tarif', $remoteId, function($query) {
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                ], false, false);
            }
            $this->savePivotData('price_clinics', 'price_id', $localId, $clinicData);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('price_clinics', 'price_id', $localId);
        $this->saveRelatedData($remoteId, $localId, $data);
    }
}