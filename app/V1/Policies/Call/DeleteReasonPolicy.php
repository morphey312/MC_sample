<?php

namespace App\V1\Policies\Call;

use App\V1\Policies\BasePolicy;

class DeleteReasonPolicy extends BasePolicy
{
    /**
     * @var  string
     */ 
    protected $module = 'call-delete-reasons';
}
