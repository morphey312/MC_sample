<?php

namespace App\V1\Contracts\Repositories\Query;

use App\V1\Contracts\Repositories\Query\Scopes;

interface LaboratoriesScheduleScopes extends Scopes
{
    /**
     * Apply default scope
     *
     * @param  mixed $object
     */
    public function scopeDefault($object);
}
