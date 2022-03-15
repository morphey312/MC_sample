<?php

namespace App\V1\Policies;

use App\V1\Models\User;
use Illuminate\Database\Eloquent\Model;

class DaySheetPolicy extends ClinicSharedPolicy
{
    /**
     * @var string
     */
    protected $module = 'day-sheets';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'day-sheets.access-clinic' => [
            'doctor-cabinet.access',
            'appointments.access-clinic',
            'appointments-sheets.access-clinic',
        ],
        'day-sheets.access' => [
            'appointments.access',
            'appointments-sheets.access',
        ],
        'day-sheets.create' => [
            'day-sheets.update',
            'day-sheets.update-clinic',
        ],
        'day-sheets.delete' => [
            'day-sheets.update',
        ],
        'day-sheets.delete-clinic' => [
            'day-sheets.update-clinic',
        ],
    ];

    /**
     * Check if user can get day doctor appointments
     *
     * @param User $user
     * 
     * @return bool
     */
    public function appointmentSchedule(User $user)
    {
        return true;
    }
}
