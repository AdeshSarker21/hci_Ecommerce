<?php

namespace App\Policies;

use App\Models\Attribute;
use App\Models\User;

class AttributePolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function view(User $user, Attribute $attribute): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['products.create', 'products.manage']);
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $user->hasAnyPermission(['products.update', 'products.manage']);
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->hasAnyPermission(['products.delete', 'products.manage']);
    }
}
