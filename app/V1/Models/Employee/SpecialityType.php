<?php

namespace App\V1\Models\Employee;

use App\V1\Models\BaseModel;

class SpecialityType extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'speciality_types';

    /**
     * @var array
     */ 
    protected $casts = [
        'for_doctor' => 'boolean', 
        'for_specialist' => 'boolean', 
        'for_assistant' => 'boolean', 
        'for_pharmacist' => 'boolean',
    ];
}
