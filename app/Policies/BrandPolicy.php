<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['products.create', 'products.manage']);
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasAnyPermission(['products.update', 'products.manage']);
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasAnyPermission(['products.delete', 'products.manage']);
    }
}
