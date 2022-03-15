<?php

namespace App\V1\Traits\Models;

use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Models\Workspace;

trait AppointmentDoctor
{
    /**
     * Related day doctor appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function doctor_appointments()
    {
        return $this->morphMany(Appointment::class, 'doctor');
    }

    /**
     * Get filtered related day sheets by clinic and date
     * 
     * @param $date
     * @param int $clinic
     * @param int $workspace
     */ 
    public function getDoctorAppointmentsByDateClinic($date, $clinic, $workspace = null)
    {
        $query = $this->doctor_appointments()
                    ->where('date', $date)
                    ->where('clinic_id', $clinic)
                    ->where('is_deleted', '=', 0);

        if (empty($workspace)) {
            $query->whereNull('workspace_id');
        } else {
            $query->where('workspace_id', '=', $workspace);
        }
        return $query->get();
    }

    /**
     * Get specializations by clinic
     * 
     * @param int $clinic
     * 
     * @return collection
     */ 
    public function clinicDoctor($clinic)
    {
        if($this instanceof Employee) {
            return $this->doctor($clinic);
        } elseif($this instanceof Workspace) {
            return $this->workspaceClinic($clinic);
        }
    }

    /**
     * Get employee doctor by clinic
     * 
     * @param int $clinic
     * 
     * @return collection
     */ 
    public function doctor($clinic)
    {
        return $this->employee_clinics()
                    ->where('clinic_id', '=', $clinic)
                    ->first()
                    ->doctor()
                    ->first();
    }

    /**
     * Get workspace clinic by clinic
     * 
     * @param int $clinic
     * 
     * @return collection
     */ 
    public function workspaceClinic($clinic)
    {
        return $this->clinics()
                    ->where('clinic_id', '=', $clinic)
                    ->first();
    }
}