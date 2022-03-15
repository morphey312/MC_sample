<?php

namespace App\V1\Policies\Notification;

use App\V1\Policies\BasePolicy;

class TemplatePolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'notification-templates';
}
