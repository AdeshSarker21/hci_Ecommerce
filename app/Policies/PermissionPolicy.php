<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['permissions.view', 'roles.manage']);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['permissions.view', 'roles.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['permissions.create', 'roles.manage']);
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['permissions.update', 'roles.manage']);
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasAnyPermission(['permissions.delete', 'roles.manage']);
    }
}
