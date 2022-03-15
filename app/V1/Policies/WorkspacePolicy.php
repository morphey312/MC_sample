<?php

namespace App\V1\Policies;

use App\V1\Policies\BasePolicy;

class WorkspacePolicy extends ClinicSharedPolicy
{
    /**
     * @var  string
     */
    protected $module = 'workspaces';
}
