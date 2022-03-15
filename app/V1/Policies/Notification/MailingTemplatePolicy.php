<?php

namespace App\V1\Policies\Notification;

use App\V1\Policies\BasePolicy;

class MailingTemplatePolicy extends BasePolicy
{
    /**
     * @var  string
     */
    protected $module = 'notification-mailing-templates';
}
