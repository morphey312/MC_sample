<?php


namespace App\V1\Contracts\Repositories\Query\Patient;


use App\V1\Contracts\Repositories\Query\Scopes;

interface AppointmentDocumentScopes extends Scopes
{
    /**
     * Apply default scope
     *
     * @param  mixed $object
     */
    public function scopeDefault($object);
}
