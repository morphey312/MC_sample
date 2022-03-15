<?php

namespace App\V1\Console\Commands\MigrateData;

class AppointmentServices extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'RecordPatientService';
    
    /**
     * @var string
     */
    protected $destTable = 'appointment_services';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id';
    
    /**
     * @var bool
     */ 
    protected $mapRefs = false;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $result = $this->pickData($data, [
            'service_id' => $this->fromRef('id_service', 'list_service'),
            'appointment_id' => $this->fromRef('id_record', 'RecordPatient'),
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'quantity' => $this->toInt('cnt', true),
            'discount' => $this->toDecimal('procent_discount', true),
            'is_base' => $this->toBool('is_base_service'),
        ], false, false);
        
        if (!$this->checkRequired($result, ['service_id', 'appointment_id', 'patient_id', 'clinic_id'])) {
            return false;
        }
        
        if ($this->checkIsDuplicate($result)) {
            return false;
        }
        
        if (!empty($data->id_tarif)) {
            $totalCost = $data->price * intval($data->cnt) * (1 - floatval($data->procent_discount) / 100);
            $result['price_id'] = $this->getReference('list_service_tarif', $data->id_tarif);
            $result['cost'] = $totalCost;
        }
        
        return $result;
    }
    
    /**
     * Check if that record already exists
     */ 
    protected function checkIsDuplicate($data)
    {
        return $this->getLocalConnection()
            ->table('appointment_services')
            ->where('service_id', '=', $data['service_id'])
            ->where('appointment_id', '=', $data['appointment_id'])
            ->exists();
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'RecordPatientService.*',
                'list_personal.id_clinic',
                'RecordPatient.id_patient',
                'list_service_tarif.id_tarif',
                'list_service_tarif.price',
            ]))
            ->join('RecordPatient', 'RecordPatient.id_record', '=', 'RecordPatientService.id_record')
            ->join('list_personal', 'list_personal.id_person', '=', 'RecordPatient.id_person')
            ->join('list_service_tarif', function($join) {
                $join->on('list_service_tarif.id_service', '=', 'RecordPatientService.id_service')
                    ->on('list_service_tarif.date1', '<=', 'RecordPatient.date_')
                    ->on('list_service_tarif.date2', '>=', 'RecordPatient.date_');
            })
            ->join('list_service_tarif_clinic', function($join) {
                $join->on('list_service_tarif_clinic.id_tarif', '=', 'list_service_tarif.id_tarif')
                    ->on('list_service_tarif_clinic.id_clinic', '=', 'list_personal.id_clinic');
            });
            
        return $query;
    }
}