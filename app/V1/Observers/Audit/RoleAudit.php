<?php

namespace App\V1\Observers\Audit;

use App\V1\Models\Patient;
use App\V1\Models\Permission;
use App\V1\Models\Specialization;
use App\V1\Models\Employee;
use App\V1\Models\Call\Result;
use App\V1\Models\Clinic;
use App\V1\Models\CallRequest;
use App\V1\Models\Call\DeleteReason;

class RoleAudit extends BaseAudit
{
    /**
     * @var array
     */
    protected $attributes = [
        'name',
    ];

    /**
     * Format name
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatNameAttribute($value)
    {
        return (string) $value;
    }

    /**
     * @inherit
     */
    protected function getOriginalValues($model)
    {
        $fresh = $model->fresh();

        return parent::getOriginalValues($model)
            + $this->getCustomAttributes($fresh);
    }

    /**
     * @inherit
     */
    protected function getCurrentValues($model)
    {
        return parent::getCurrentValues($model)
            + $this->getCustomAttributes($model);
    }

    /**
     * Get custom attributes from user model
     *
     * @param \App\V1\Models\Appointment $model
     *
     * @return array
     */
    protected function getCustomAttributes($model)
    {
        $data = [];

        $data['permissions'] = $this->getPermissionsWithGroup($model);

        return $data;
    }

    protected function getPermissionsWithGroup($model)
    {
        $data = [];

        foreach ($model->permissions->sortBy('id') as $permission) {
            if (array_key_exists($permission->group_id, $data)) {
                $data[$permission->group_id] = $data[$permission->group_id].';;'.$permission->description;
            } else {
                $data[$permission->group_id] = $permission->group->name.';;'.$permission->description;
            }
        }

        return $data;
    }
}
