<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['roles.view', 'roles.manage']);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasAnyPermission(['roles.view', 'roles.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['roles.create', 'roles.manage']);
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $user->hasAnyPermission(['roles.update', 'roles.manage']);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system) {
            return false;
        }

        return $user->hasAnyPermission(['roles.delete', 'roles.manage']);
    }
}
