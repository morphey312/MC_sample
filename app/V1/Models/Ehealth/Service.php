<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;

class Service extends BaseModel
{
    /**
     * @var string
     */ 
    protected $table = 'ehealth_services';

    /**
     * @var array
     */
    protected $casts = [
        'request_allowed' => 'boolean',
    ];
}
