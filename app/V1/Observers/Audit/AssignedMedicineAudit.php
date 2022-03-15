<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\ActionLog;
use App\V1\Models\Appointment;
use App\V1\Models\Clinic;
use App\V1\Models\Employee;
use App\V1\Models\Medicine;
use App\V1\Models\Patient\AssignedMedicine;
use App\V1\Models\Patient\IssuedMedicine;
use App\V1\Models\Specialization;
use App\V1\Observers\Audit\BaseAudit;
use Auth;
use Illuminate\Database\Eloquent\Model;

class AssignedMedicineAudit extends BaseAudit
{
    /**
     * @var array
     */ 
    protected $attributes = [
        'warranter',
    ];
    
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
     * Format assigned_medicine
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatMedicineIdAttribute($value)
    {
        return $this->fetchAttribute(Medicine::class, $value, 'name');
    }

    /**
     * Format clinic_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatClinicIdAttribute($value)
    {
        return $this->fetchAttribute(Clinic::class, $value, 'name');
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
     * Format doctor_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatAssignerIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format base_cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatBaseCostAttribute($value)
    {
        return round($value, 2);
    }

    /**
     * Format quantity
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatQuantityAttribute($value)
    {
        return round($value, 3);
    }

    /**
     * Format cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCostAttribute($value)
    {
        return round($value, 3);
    }

    /**
     * Format cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatIsFreeAttribute($value)
    {
        return round($value, 0);
    }

    /**
     * Format cost
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatSelfCostAttribute($value)
    {
        return round($value, 3);
    }

    /**
     * Format card_specialization_id
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatCardSpecializationIdAttribute($value)
    {
        return $this->fetchAttribute(Specialization::class, $value, 'name');
    }

    /**
     * Format by_policy
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatByPolicyAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format franchise
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatFranchiseAttribute($value)
    {
        return round($value, 2);
    }
}
