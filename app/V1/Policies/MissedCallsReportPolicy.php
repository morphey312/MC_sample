<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class MissedCallsReportPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'missed_calls_reports';
}
