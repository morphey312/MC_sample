<?php

namespace App\V1\Models\Ehealth;

use App\V1\Models\BaseModel;

class Position extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'ehealth_positions';

    /**
     * @var array
     */
    protected $casts = [
        'is_owner' => 'boolean',
    ];
}
