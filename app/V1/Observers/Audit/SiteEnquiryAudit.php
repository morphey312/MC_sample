<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Employee;

class SiteEnquiryAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'operator_id',
        'status'
    ];

    /**
     * Format operator
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatOperatorIdAttribute($value)
    {
        return $this->fetchAttribute(Employee::class, $value, 'full_name');
    }

    /**
     * Format operator
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatStatusAttribute($value)
    {
        return (string) $value;
    }
}
