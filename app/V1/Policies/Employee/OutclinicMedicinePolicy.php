<?php

namespace App\V1\Policies\Employee;

use App\V1\Policies\ClinicSharedPolicy;

class OutclinicMedicinePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'outclinic-medicine';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'outclinic-medicine.update' => [
            'doctor-cabinet.start-course'
        ],
    ];
}
