<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;

class Notification extends BaseModel
{
    /**
     * @var array
     */ 
    protected $casts = [
        'data' => 'array',

    ];
}
