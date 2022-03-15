<?php

namespace App\V1\Observers\Audit\Clinic\Clinic;

use App\V1\Observers\Audit\BaseAudit;

class GroupAudit extends BaseAudit
{

    /**
     * @var array
     */
    protected $attributes = [
        'name',
    ];
}
