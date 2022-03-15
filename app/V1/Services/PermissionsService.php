<?php

namespace App\V1\Services;

use App\V1\Models\User;
use App\V1\Contracts\Services\PermissionsService as PermissionsServiceInterface;
use App\V1\Contracts\Services\Permissions\RoleHolder;
use App\V1\Contracts\Services\Permissions\PermissionsHolder;

class PermissionsService implements PermissionsServiceInterface
{
    /**
     * @var array
     */
    protected $cached = [];

    /**
     * @inheritDoc
     */
    public function has(RoleHolder $user, $permission)
    {
        return in_array($permission, $this->get($user));
    }

    /**
     * @inheritDoc
     */
    public function hasAny(RoleHolder $user, Array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (in_array($permission, $this->get($user))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function get(RoleHolder $user)
    {
        if (array_key_exists($user->id, $this->cached)) {
            return $this->cached[$user->id];
        }

        return $this->cached[$user->id] = $this->resolvePermissions($user);
    }

    /**
     * Get list of user permissions
     *
     * @return array
     */
    protected function resolvePermissions($user)
    {
        $result = [];

        foreach ($user->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $result[$permission] = $permission;
            }
        }

        if ($user instanceof PermissionsHolder) {
            foreach ($user->getPermissions() as $permission) {
                $result[$permission] = $permission;
            }
        }

        return array_values($result);
    }
}
