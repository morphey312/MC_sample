<?php

namespace App\V1\Contracts\Repositories\Query\Employee\Cashbox;

use App\V1\Contracts\Repositories\Query\Scopes;

interface CashTransferScopes extends Scopes
{
    /**
     * Apply default scope
     * 
     * @param  mixed $object
     */ 
    public function scopeDefault($object);
}