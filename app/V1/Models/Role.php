<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Contracts\Services\Permissions\PermissionsHolder;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;

class Role extends BaseModel implements PermissionsHolder, SharedResourceInterface
{
    use SharedResource, HasConstraint;

    const RELATION_TYPE = 'role';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'permissions',
        'users',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'users',
    ];

    /**
     * Related users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    /**
     * Related permissions
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    /**
     * @inherit
     */
    public function getPermissions()
    {
        return $this->permissions->pluck('name')->all();
    }
}
