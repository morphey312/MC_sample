<?php

namespace App\V1\Observers\Audit\Workspace;

use App\V1\Observers\Audit\BaseAudit;

class WorkspaceAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'has_day_sheet',
        'is_active',
        'is_hospital_room',
        'is_operational',
        'name',
    ];

    /**
     * Format has_day_sheet
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatHasDaySheetAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_active
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsActiveAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_hospital_room
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsHospitalRoomAttribute($value)
    {
        return (bool) $value;
    }

    /**
     * Format is_operational
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function formatIsOperationalAttribute($value)
    {
        return (bool) $value;
    }
}
