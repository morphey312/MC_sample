<?php

namespace App\V1\Policies\Analysis;

use App\V1\Policies\ClinicSharedPolicy;

class TemplatePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'analysis-templates';

    /**
     * @var array
     */
    protected $providedBy = [
        'analysis-templates.access' => [
            'analysis-results.update-result',
        ],
        'analysis-templates.access-clinic' => [
            'analysis-results.update-result-clinic',
        ],
    ];
}
