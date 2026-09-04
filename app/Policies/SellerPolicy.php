<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['vendors.view', 'vendors.manage']);
    }

    public function view(User $user, Seller $seller): bool
    {
        if ($user->hasAnyPermission(['vendors.view', 'vendors.manage'])) {
            return true;
        }

        if ($user->id === $seller->user_id) {
            return true;
        }

        return $seller->staff()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasRole('seller') || $user->hasAnyPermission(['vendors.manage']);
    }

    public function update(User $user, Seller $seller): bool
    {
        if ($user->hasAnyPermission(['vendors.manage'])) {
            return true;
        }

        if ($user->id === $seller->user_id) {
            return $seller->isApproved();
        }

        return false;
    }

    public function delete(User $user, Seller $seller): bool
    {
        return $user->hasAnyPermission(['vendors.manage']);
    }

    public function approve(User $user, Seller $seller): bool
    {
        return $user->hasAnyPermission(['vendors.manage']) && $seller->isPending();
    }

    public function reject(User $user, Seller $seller): bool
    {
        return $user->hasAnyPermission(['vendors.manage']) && $seller->isPending();
    }

    public function suspend(User $user, Seller $seller): bool
    {
        return $user->hasAnyPermission(['vendors.manage']) && $seller->isApproved();
    }

    public function manageStaff(User $user, Seller $seller): bool
    {
        if ($user->hasAnyPermission(['vendors.manage'])) {
            return true;
        }

        if ($user->id === $seller->user_id) {
            return true;
        }

        return $seller->staff()->where('user_id', $user->id)
            ->where('can_manage_settings', true)
            ->exists();
    }
}
