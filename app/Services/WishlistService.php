<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;

class WishlistService
{
    public function getWishlistItems(): \Illuminate\Database\Eloquent\Collection
    {
        return Wishlist::where('user_id', auth()->id())
            ->with(['product.seller', 'product.category', 'product.brand', 'product.images'])
            ->latest()
            ->get();
    }

    public function getWishlistCount(): int
    {
        return Wishlist::where('user_id', auth()->id())->count();
    }

    public function getWishlistIds(): array
    {
        return Wishlist::where('user_id', auth()->id())
            ->pluck('product_id')
            ->toArray();
    }

    public function isInWishlist(int $productId): bool
    {
        return Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->exists();
    }

    public function toggle(int $productId): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return ['success' => false, 'message' => __('Product not found.')];
        }

        if (!$product->is_active || $product->status !== 'published') {
            return ['success' => false, 'message' => __('This product is not available.')];
        }

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return [
                'success' => true,
                'action' => 'removed',
                'message' => __('Product removed from wishlist.'),
                'wishlist_count' => $this->getWishlistCount(),
            ];
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
        ]);

        return [
            'success' => true,
            'action' => 'added',
            'message' => __('Product added to wishlist.'),
            'wishlist_count' => $this->getWishlistCount(),
        ];
    }

    public function remove(int $productId): array
    {
        $deleted = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return ['success' => false, 'message' => __('Product not found in wishlist.')];
        }

        return [
            'success' => true,
            'message' => __('Product removed from wishlist.'),
            'wishlist_count' => $this->getWishlistCount(),
        ];
    }

    public function clear(): array
    {
        Wishlist::where('user_id', auth()->id())->delete();

        return [
            'success' => true,
            'message' => __('Wishlist cleared.'),
            'wishlist_count' => 0,
        ];
    }

    public function moveToCart(int $productId, CartService $cartService): array
    {
        $item = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->first();

        if (!$item) {
            return ['success' => false, 'message' => __('Product not found in wishlist.')];
        }

        $product = $item->product;

        if (!$product || !$product->is_active || $product->status !== 'published') {
            $item->delete();
            return ['success' => false, 'message' => __('This product is no longer available.')];
        }

        if (!$product->inStock()) {
            return ['success' => false, 'message' => __('This product is out of stock.')];
        }

        $cartResult = $cartService->addItem($product->id, 1);

        if ($cartResult['success']) {
            $item->delete();
            $cartResult['message'] = __('Product moved to cart.');
            $cartResult['wishlist_count'] = $this->getWishlistCount();
        }

        return $cartResult;
    }
}
