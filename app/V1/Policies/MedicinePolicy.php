<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class MedicinePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'medicines';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'medicines.access' => [
            'patient-cabinet.access',
        ],
    ];
}
