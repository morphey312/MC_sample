<?php

namespace App\V1\Policies;

use App\V1\Models\User;
use Permissions;

class TreatmentCoursePolicy extends BasePolicy
{
    /**
     * @var string
     */ 
    protected $module = 'treatment-courses';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'treatment-courses.access' => [
            'patient-cabinet.courses',
            'doctor-cabinet.start-course',
        ],
        'treatment-courses.create' => [
            'doctor-cabinet.start-course',
            'appointments.start-course',
        ],
        'treatment-courses.update' => [
            'doctor-cabinet.start-course',
            'patient-cabinet.treatment-course-edit',
            'appointments.start-course',
        ],
        'treatment-courses.delete' => [
            'doctor-cabinet.start-course',
        ],
    ];
}
