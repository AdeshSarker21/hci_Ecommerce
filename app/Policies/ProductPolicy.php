<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy extends Policy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['products.view', 'products.manage']);
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->hasAnyPermission(['products.view', 'products.manage'])) {
            return true;
        }

        if ($user->id === $product->seller->user_id) {
            return true;
        }

        return $user->seller
            && $product->seller_id === $user->seller->id
            && $user->seller->staffDetails()->where('user_id', $user->id)->where('can_manage_products', true)->exists();
    }

    public function create(User $user): bool
    {
        if ($user->hasAnyPermission(['products.create', 'products.manage'])) {
            return true;
        }

        return $user->isSeller() && $user->seller && $user->seller->isApproved();
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasAnyPermission(['products.update', 'products.manage'])) {
            return true;
        }

        if ($user->id === $product->seller->user_id && $product->seller->isApproved()) {
            return true;
        }

        return $user->seller
            && $product->seller_id === $user->seller->id
            && $user->seller->staffDetails()->where('user_id', $user->id)->where('can_manage_products', true)->exists();
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->hasAnyPermission(['products.delete', 'products.manage'])) {
            return true;
        }

        if ($user->id === $product->seller->user_id && $product->seller->isApproved()) {
            return in_array($product->status, ['draft', 'rejected']);
        }

        return false;
    }

    public function approve(User $user, Product $product): bool
    {
        return $user->hasAnyPermission(['products.manage'])
            && in_array($product->status, ['pending_review', 'rejected']);
    }

    public function reject(User $user, Product $product): bool
    {
        return $user->hasAnyPermission(['products.manage'])
            && in_array($product->status, ['pending_review', 'approved']);
    }

    public function publish(User $user, Product $product): bool
    {
        return $user->hasAnyPermission(['products.manage'])
            && $product->status === 'approved';
    }

    public function unpublish(User $user, Product $product): bool
    {
        return $user->hasAnyPermission(['products.manage'])
            && $product->status === 'published';
    }
}
