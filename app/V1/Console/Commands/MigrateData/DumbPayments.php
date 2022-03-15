<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Payment;

class DumbPayments extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'Payment';
    
    /**
     * @var string
     */
    protected $destTable = 'payments';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_payment';
    
    /**
     * @var int
     */ 
    protected $fakeCashier = 1;
    
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
            'amount' => $this->toDecimal('sum_payment'),
            'payed_amount' => $this->toDecimal('sum_payment'),
            'doctor_id' => $this->fromRef('id_human_doctor', 'list_personal'),
            'cashier_id' => $this->fromRef('id_cashier', 'list_personal'),
            'clinic_id' => $this->fromRef('id_clinic', 'list_clinic', true),
            'patient_id' => $this->fromRef('id_patient', 'list_patient'),
            'payment_destination_id' => $this->fromRef('id_purpose_pay', 'list_purpose_pay', true),
            'created_at' => 'date_insert',
            'updated_at' => 'date_insert',
            'type' => $this->fromMap('is_vozvrat', [
                '0' => 'income',
                '1' => 'expense',
            ], 'income'),
            'comment' => $this->toUTF('note'),
        ], true, false);
        
        if (!$this->checkRequired($result, ['cashier_id'])) {
            $result['cashier_id'] = $this->fakeCashier;
        }
        
        if (!$this->checkRequired($result, ['clinic_id', 'patient_id'])) {
            return false;
        }
        
        if ($this->checkIsDuplicate($result)) {
            return false;
        }
        
        $paymentType = $this->getReference('list_type_pay', $data->id_type_pay, true);
        $result['cashbox_id'] = $this->getCachboxId($result['cashier_id'], $result['clinic_id'], $paymentType);
        $result['comment'] = '[@autosplit]' . $result['comment'];
        
        return $result;
    }
    
    /**
     * Check if that record already exists
     */ 
    protected function checkIsDuplicate($data)
    {
        return $this->getLocalConnection()
            ->table('payments')
            ->where('patient_id', '=', $data['patient_id'])
            ->where('clinic_id', '=', $data['clinic_id'])
            ->where('created_at', '=', $data['created_at'])
            ->exists();
    }
    
    /**
     * @inherit
     */ 
    protected function saveData($data, $row)
    {
        $specializationId = $this->getReference('list_specialization', $row->id_specialization, true);
        $amount = $data['amount'];
        
        if ($data['type'] === 'income') {
            $services = $this->getServicesWithDebt($data['patient_id'], $specializationId);
            $isRefund = false;
        } else {
            $services = $this->getServicesToRefund($data['patient_id'], $specializationId);
            $isRefund = true;
        }
        
        $exactOrMore = $services->filter(function($row) use($amount, $isRefund) {
            if ($isRefund) {
                return $row->paid >= $amount;
            } else {
                return $row->cost - $row->paid >= $amount;
            }
        });
        
        if ($exactOrMore->count() !== 0) {
            $service = $exactOrMore->first();
            $data['appointment_id'] = $service->appointment_id;
            $data['service_id'] = $service->id;
            $data['is_deposit'] = 0;
            
            return $this->getLocalConnection()
                ->table($this->destTable)
                ->insertGetId($data);
        }
        
        $lastId = null;
        while ($services->count() != 0) {
            $service = $services->pop();
            
            if ($isRefund) {
                $paymentAmount = min($amount, $service->paid);
            } else {
                $paymentAmount = min($amount, $service->cost - $service->paid);
            }
            
            $amount -= $paymentAmount;
            
            $data['appointment_id'] = $service->appointment_id;
            $data['service_id'] = $service->id;
            $data['is_deposit'] = 0;
            $data['amount'] = $data['payed_amount'] = $paymentAmount;
            
            $lastId = $this->getLocalConnection()
                ->table($this->destTable)
                ->insertGetId($data);
            
            if ($amount <= 0) {
                return $lastId;
            }
        }
        
        if ($amount > 0) {
            $data['appointment_id'] = null;
            $data['service_id'] = null;
            $data['is_deposit'] = 1;
            $data['amount'] = $data['payed_amount'] = $amount;
            
            $lastId = $this->getLocalConnection()
                ->table($this->destTable)
                ->insertGetId($data);
        }
        
        return $lastId;
    }
    
    /**
     * Get query to count payment balance
     * 
     * @param array $data
     * @param object $row
     * 
     * @return \Illuminate\Database\Query\Builder
     */ 
    protected function getBalanceQuery($patientId, $specializationId)
    {
        return $this->getLocalConnection()
            ->table('appointment_services')
            ->select('appointment_services.*')
            ->selectRaw(sprintf(
                'sum(payments.amount * if(payments.type = \'%s\', -1, 1)) as paid', 
                Payment::TYPE_EXPENSE
            ))
            ->leftJoin('payments', function($join) {
                $join->on('appointment_services.id', '=', 'payments.service_id')
                    ->where('payments.is_deleted', '=', 0);
            })
            ->join('appointments', function($join) use($patientId, $specializationId) {
                $join->on('appointments.id', '=', 'appointment_services.appointment_id')
                    ->where('appointments.is_deleted', '=', 0)
                    ->where('appointments.patient_id', '=', $patientId)
                    ->where('appointments.card_specialization_id', '=', $specializationId);
            })
            ->join('appointment_statuses', function($join) {
                $join->on('appointment_statuses.id', '=', 'appointments.appointment_status_id')
                    ->where('appointment_statuses.service_in_cost', '=', 1);
            })
            ->where('appointment_services.cost', '>', 0)
            ->groupBy('appointment_services.id');
    }
    
    /**
     * Get services with debts
     * 
     * @param int $patientId
     * @param int $specializationId
     * 
     * @return array
     */ 
    protected function getServicesWithDebt($patientId, $specializationId)
    {
        return $this->getBalanceQuery($patientId, $specializationId)
            ->havingRaw('paid < appointment_services.cost OR paid IS NULL')
            ->get()
            ->sortBy(function($value) {
                return $value->cost - $value->paid;
            });
    }
    
    /**
     * Get services to refund
     * 
     * @param int $patientId
     * @param int $specializationId
     * 
     * @return array
     */ 
    protected function getServicesToRefund($patientId, $specializationId)
    {
        return $this->getBalanceQuery($patientId, $specializationId)
            ->where('payments.comment', 'like', '%[@autosplit]%')
            ->havingRaw('paid > 0')
            ->get()
            ->sortBy(function($value) {
                return $value->paid;
            });
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'Payment.*',
                'IIF(list_doctor.is_human = 0, NULL, IIF(Payment.id_doctor2 IS NULL, Payment.id_doctor, Payment.id_doctor2)) as id_human_doctor',
                'list_personal.id_person AS id_cashier',
                'list_patient_card.id_clinic',
                'list_patient_card.id_patient',
                'list_patient_card.id_specialization',
            ]))
            ->leftJoin('list_login', 'list_login.username', '=', 'Payment.username')
            ->leftJoin('list_personal', 'list_login.id_person', '=', 'list_personal.id_person')
            ->leftJoin('list_personal AS list_doctor', 'Payment.id_doctor2', '=', 'list_doctor.id_person')
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
    
    /**
     * @inherit
     */ 
    protected function addRecordsFilter($query)
    {
        $query->whereNotExists(function ($query) {
            $query->from('PaymentDetailFinal')
                  ->whereRaw('PaymentDetailFinal.id_payment = Payment.id_payment');
        });
        
        return $query;
    }
}