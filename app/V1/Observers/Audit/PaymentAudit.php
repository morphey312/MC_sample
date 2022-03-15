<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Employee;
use App\V1\Models\Employee\Cashbox;
use App\V1\Models\Appointment;
use App\V1\Models\Appointment\Service;
use App\V1\Models\Service\PaymentDestination;

class PaymentAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'amount',
        'payed_amount',
        'discount',
        'cashbox_id',
        'service_id',
        'doctor_id',
        'appointment_id',
        'payment_destination_id',
        'patient_id',
        'is_deleted',
        'is_deposit',
        'created_at',
    ];

    /**
     * Format amount
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatAmountAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format is_deposit
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsDepositAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format payed_amount 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatPayedAmountAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Format is_deleted 
     * 
     * @param mixed $value
     * 
     * @return bool
     */ 
    protected function formatIsDeletedAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format cashbox_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCashboxIdAttribute($value)
    {
        return $this->fetchAttribute(Cashbox::class, $value, 'name');
    }

    /**
     * Format payment_destiantion_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatPaymentDestinationIdAttribute($value)
    {
        return $this->fetchAttribute(PaymentDestination::class, $value, 'name');
    }

    /**
     * Format cashbox_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatDoctorIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format appointment_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatAppointmentIdAttribute($value)
    {
        return $this->fetchAttribute(Appointment::class, $value, 'detail_info');
    }

    /**
     * Format service id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatServiceIdAttribute($value)
    {
        return $this->fetchAttribute(Service::class, $value, 'service_name');
    }
    
    /**
     * Format patient_id 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatPatientIdAttribute($value)
    {
        return $this->formatPatientName($value);
    }

    /**
     * Format created_at 
     * 
     * @param mixed $value
     * 
     * @return string
     */ 
    protected function formatCreatedAtAttribute($value)
    {
        return $this->formatDate($value);
    }
}