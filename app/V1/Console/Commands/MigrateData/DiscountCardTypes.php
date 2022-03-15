<?php

namespace App\V1\Console\Commands\MigrateData;

class DiscountCardTypes extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_type_bonus_card';
    
    /**
     * @var string
     */
    protected $destTable = 'discount_card_types';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_type_bonus_card';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'use_detail_payments' => $this->toBool('is_detail_discount'),
            'discount_percent' => 'procent_discount',
            'dont_use_for_patient' => $this->toBool('not_used'),
            'show_card_in_patient_list' => $this->toBool('is_show_card'),
            'cant_be_copied' => $this->toBool('not_copy_card'),
            'propose_to_disable_on_copy' => $this->toBool('not_used_default'),
            'max_owners' => $this->toInt('max_cnt_patient'),
            'dont_auto_add_to_appointment' => $this->toBool('dont_add_to_appointment'),
            'priority' => $this->toInt('priority'),
            'expire_period' => $this->toInt('max_period_day'),
            'use_card_number' => $this->toBool('is_use_number'),
            'number_kind_id' => $this->fromRef('id_numeration', 'list_bonus_card_numeration', true),
        ]);
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'list_type_bonus_card.*',
                'list_type_bonus_card.not_auto_add_card_in_recorpatient AS dont_add_to_appointment',
            ]));
            
        return $query;
    }
    
    /**
     * @inherit
     */ 
    protected function saveRelatedData($remoteId, $localId, $data)
    {
        $clinics = $this->getPivotData('list_type_bonus_card_clinic', 'id_type_bonus_card', $remoteId, function($query) {
            $query->whereNotIn('id_clinic', self::$excludeClinicIds);
            return $query;
        });
        
        if ($clinics->count() !== 0) {
            $clinicData = [];
            foreach ($clinics as $clinic) {
                $clinicData[] = $this->pickData($clinic, [
                    'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
                    'payment_method_id' => $this->fromRef('id_type_pay', 'list_type_pay', true),
                ], false, false);
            }
            $this->savePivotData('clinic_discount_card', 'discount_card_type_id', $localId, $clinicData);
        }
        
        if ($data->is_detail_discount == 1) {
            $discounts = $this->getPivotData('list_type_bonus_card_discount', 'id_type_bonus_card', $remoteId);
            if ($discounts->count() !== 0) {
                $discountData = [];
                foreach ($discounts as $discount) {
                    $discountData[] = $this->pickData($discount, [
                        'payment_destination_id' => $this->fromRef('id_purpose_pay', 'list_purpose_pay', true),
                        'discount_percent' => 'procent_discount',
                        'date_start' => $this->toDate('date_begin'),
                        'date_end' => $this->toDate('date_end'),
                    ], false, false);
                }
                $this->savePivotData('discount_card_payment', 'discount_card_type_id', $localId, $discountData);
            }
        }
    }
    
    /**
     * @inherit
     */ 
    protected function addRecordsFilter($query)
    {
        $query->whereExists(function ($query) {
            $query->from('list_type_bonus_card_clinic')
                  ->whereRaw('list_type_bonus_card_clinic.id_type_bonus_card = list_type_bonus_card.id_type_bonus_card')
                  ->whereNotIn('list_type_bonus_card_clinic.id_clinic', self::$excludeClinicIds);
        });
        
        return $query;
    }
    
    /**
     * @inherit
     */ 
    protected function patchRelatedData($remoteId, $localId, $data)
    {
        $this->clearPivotData('clinic_discount_card', 'discount_card_type_id', $localId);
        $this->clearPivotData('discount_card_payment', 'discount_card_type_id', $localId);
        $this->saveRelatedData($remoteId, $localId, $data);
    }
}