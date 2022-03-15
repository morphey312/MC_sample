<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Analysis;

class Payments extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'PaymentDetailFinal';
    
    /**
     * @var string
     */
    protected $destTable = 'payments';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_pay_final';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $appointmentId = $this->getReference('RecordPatient', $data->id_record);
        
        if (!$appointmentId) {
            return false;
        }
        
        if ($data->is_analyse == 1) {
            $appointmentServiceId = $this->getLocalConnection()
                ->table('appointment_services')
                ->where('appointment_id', $appointmentId)
                ->where('container_type', AppointmentAnalyses::CONTAINER_TYPE)
                ->value('id');
        } else {
            $serviceId = $this->getReference('list_service', $data->id_service);
            if ($serviceId) {
                $appointmentServiceId = $this->getLocalConnection()
                    ->table('appointment_services')
                    ->where('appointment_id', $appointmentId)
                    ->where('service_id', $serviceId)
                    ->value('id');
            } else {
                $appointmentServiceId = null;
            }
        }
        
        $result = $this->pickData($data, [
            'amount' => $this->toDecimal('sum_payment_base'),
            'payed_amount' => $this->toDecimal('sum_payment'),
            'discount' => $this->toDecimal('procent_discount'),
            'doctor_id' => $this->fromRef('id_doctor', 'list_personal'),
            'cashier_id' => $this->fromRef('id_cashier', 'list_personal'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'payment_destination_id' => $this->fromRef('id_purpose_pay', 'list_purpose_pay', true),
            'created_at' => 'payment_date_insert',
            'updated_at' => 'payment_date_insert',
            'type' => $this->fromMap('is_vozvrat', [
                '0' => 'income',
                '1' => 'expense',
            ], 'income'),
            'comment' => $this->toUTF('note'),
        ], true, false);
        
        if (!$this->checkRequired($result, ['patient_id', 'cashier_id'])) {
            return false;
        }
        
        $result['appointment_id'] = $appointmentId;
        $result['service_id'] = $appointmentServiceId;
        
        $paymentType = $this->getReference('list_type_pay', $data->id_type_pay, true);
        
        $result['is_deposit'] = $result['service_id'] ? 0 : 1;
        $result['cashbox_id'] = $this->getCachboxId($result['cashier_id'], $result['clinic_id'], $paymentType);
        
        return $result;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'PaymentDetailFinal.*',
                'IIF(list_personal.is_human = 0, NULL, Payment.id_doctor2) as id_doctor',
                'Payment.id_purpose_pay',
                'Payment.is_vozvrat',
                'Payment.note',
                'Payment.id_type_pay',
                'Payment.date AS payment_date_insert',
                'list_login.id_person AS id_cashier',
                'list_patient_card.id_clinic',
                'RecordPatientService.id_service',
                'RecordPatient.id_record',
                'RecordPatient.id_patient',
                'list_service.is_analyse',
            ]))
            ->leftJoin('Payment', 'Payment.id_payment', '=', 'PaymentDetailFinal.id_payment')
            ->leftJoin('RecordPatientService', 'RecordPatientService.id', '=', 'PaymentDetailFinal.id_record_service')
            ->leftJoin('RecordPatient', 'RecordPatient.id_record', '=', 'RecordPatientService.id_record')
            ->leftJoin('list_login', 'list_login.username', '=', 'Payment.username')
            ->leftJoin('list_personal', 'Payment.id_doctor2', '=', 'list_personal.id_person')
            ->leftJoin('list_service', 'list_service.id_service', '=', 'RecordPatientService.id_service')
            ->leftJoin('list_patient_card', 'list_patient_card.id_card_record', '=', 'Payment.id_card_record');
            
        return $query;
    }
    
    /**
     * Get cachbox
     * 
     * @param int $employeeId
     * @param int $clinicId
     * @param int $paymentTypeId
     * 
     * @return int
     */ 
    protected function getCachboxId($employeeId, $clinicId, $paymentTypeId)
    {
        $cachboxId = $this->getLocalConnection()
            ->table('cashboxes')
            ->where('cashier_id', $employeeId)
            ->where('clinic_id', $clinicId)
            ->where('payment_method_id', $paymentTypeId)
            ->value('id');
        
        if ($cachboxId !== null) {
            return $cachboxId;
        }
        
        return $this->getLocalConnection()
            ->table('cashboxes')
            ->insertGetId([
                'cashier_id' => $employeeId, 
                'clinic_id' => $clinicId, 
                'payment_method_id' => $paymentTypeId,
            ]);
    }
}