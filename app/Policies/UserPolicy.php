<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['users.view', 'users.manage']);
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasAnyPermission(['users.view', 'users.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['users.create', 'users.manage']);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasAnyPermission(['users.update', 'users.manage']);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasAnyPermission(['users.delete', 'users.manage']);
    }

    public function manageRoles(User $user): bool
    {
        return $user->hasAnyPermission(['roles.manage', 'users.manage']);
    }
}
