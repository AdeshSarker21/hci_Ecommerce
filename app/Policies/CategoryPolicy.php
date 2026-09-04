<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['products.create', 'products.manage']);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasAnyPermission(['products.update', 'products.manage']);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasAnyPermission(['products.delete', 'products.manage']);
    }
}
