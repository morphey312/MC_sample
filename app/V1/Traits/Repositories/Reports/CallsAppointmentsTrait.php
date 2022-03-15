<?php

namespace App\V1\Traits\Repositories\Reports;

use App\V1\Models\Appointment;
use App\V1\Models\Call;

trait CallsAppointmentsTrait
{
    /**
     * Setup base query for calls
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function callsBaseQuery()
    {
        return Call::where('calls.is_first', 1)
            ->leftJoin('call_delete_reasons', 'call_delete_reasons.id', '=', 'calls.call_delete_reason_id')
            ->where(function($query) {
                $query->whereNull('call_delete_reasons.include_to_report')
                    ->orWhere('call_delete_reasons.include_to_report', '=', 1);
            });
    }
    
    /**
     * Setup base appointments query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function appointmentsBaseQuery()
    {
        return Appointment::where('appointments.is_first', 1)
            ->leftJoin('appointment_delete_reasons', 'appointment_delete_reasons.id', '=', 'appointments.appointment_delete_reason_id')
            ->where(function($query) {
                $query->where('appointments.is_deleted', '=', 0)
                    ->orWhereNull('appointment_delete_reasons.include_to_report')
                    ->orWhere('appointment_delete_reasons.include_to_report', '=', 1);
            });
    }
    
    /**
     * Setup base income query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */ 
    protected function incomeBaseQuery()
    {
        return $this->incomeTreatmentBaseQuery()
            ->where('appointments.is_first', 1);
    }
    
    /**
     * Setup treatment query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */ 
    protected function treatmentBaseQuery()
    {
        return $this->incomeTreatmentBaseQuery()
            ->join('appointment_services', function($join) {
                 $join->on('appointment_services.appointment_id', '=', 'appointments.id')
                    ->whereNull('appointment_services.container_type');
            })
            ->join('services', function($join) {
                $join->on('appointment_services.service_id', '=', 'services.id')
                    ->where('services.is_base', '=', 1);
            });
    }
    
    /**
     * Setup income/treatment base query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */ 
    protected function incomeTreatmentBaseQuery()
    {
        return Appointment::where('appointments.is_deleted', '=', 0)
            ->join('appointment_statuses', function($join) {
                $join->on('appointment_statuses.id', '=', 'appointments.appointment_status_id')
                    ->where('service_in_cost', '=', 1);
            });
    }
}