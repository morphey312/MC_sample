<?php

namespace App\V1\Policies\Patient\Card;

use App\V1\Policies\BasePolicy;

class SpecializationPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'patient-cards';
    
    /**
     * @var array
     */
    protected $providedBy = [
        'patient-cards.access' => [
            'patient-cabinet.outpatient-cards',
        ],
    ];
}
