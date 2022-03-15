<?php

namespace App\V1\Policies\SiteEnquiry;

use App\V1\Policies\BasePolicy;

class ServicePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'site-enquiry-services';

    /**
     * @var array
     */
    protected $providedBy = [
        'site-enquiry-services.access' => [
            'payments.online-refund'
        ],
        'site-enquiry-services.update' => [
            'payments.online-refund'
        ],
    ];
}
