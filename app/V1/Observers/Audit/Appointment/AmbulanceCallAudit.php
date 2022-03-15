<?php

namespace App\V1\Observers\Audit\Appointment;

use App\V1\Observers\Audit\BaseAudit;

class AmbulanceCallAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'caller',
        'phone',
        'street',
        'house',
        'district',
        'porch',
        'flat',
        'storey',
        'reason',
        'comment',
        'call_transferred_time',
        'en_route_time',
        'arrival_time',
        'en_route_overall_minutes',
    ];
}
