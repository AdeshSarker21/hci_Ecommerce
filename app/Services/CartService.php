<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getSessionId(): string
    {
        if (!Session::has('cart_session_id')) {
            Session::put('cart_session_id', md5(uniqid('cart_', true) . time()));
        }
        return Session::get('cart_session_id');
    }

    public function getCartItems(): \Illuminate\Database\Eloquent\Collection
    {
        $query = CartItem::with(['product.seller', 'product.category', 'product.brand', 'product.images']);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }

        return $query->get();
    }

    public function getCartCount(): int
    {
        $query = CartItem::query();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }

        return (int) $query->sum('quantity');
    }

    public function getCartTotal(): float
    {
        return $this->getCartItems()->sum('subtotal');
    }

    public function getCartDiscount(): float
    {
        return $this->getCartItems()->sum('discount_amount');
    }

    public function getCartShipping(): float
    {
        $total = $this->getCartTotal();
        return $total >= 50 ? 0 : 5.99;
    }

    public function getCartGrandTotal(): float
    {
        return $this->getCartTotal() + $this->getCartShipping();
    }

    public function getCartBySeller(): \Illuminate\Support\Collection
    {
        return $this->getCartItems()->groupBy(function ($item) {
            return $item->product?->seller_id;
        });
    }

    public function addItem(int $productId, int $quantity = 1, ?array $selectedVariants = null): array
    {
        $product = Product::with(['seller', 'category', 'brand', 'images'])->find($productId);

        if (!$product) {
            return ['success' => false, 'message' => __('Product not found.')];
        }

        if (!$product->is_active || $product->status !== 'published') {
            return ['success' => false, 'message' => __('This product is not available.')];
        }

        if (!$product->inStock()) {
            return ['success' => false, 'message' => __('This product is out of stock.')];
        }

        $query = CartItem::query();
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }
        $query->where('product_id', $productId);

        if ($selectedVariants) {
            $query->where('selected_variants', json_encode($selectedVariants));
        }

        $existingItem = $query->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($newQuantity > $product->available_stock) {
                return [
                    'success' => false,
                    'message' => __('Only :count items available in stock.', ['count' => $product->available_stock]),
                ];
            }
            $existingItem->update(['quantity' => $newQuantity]);
            $item = $existingItem;
        } else {
            if ($quantity > $product->available_stock) {
                return [
                    'success' => false,
                    'message' => __('Only :count items available in stock.', ['count' => $product->available_stock]),
                ];
            }

            $item = CartItem::create([
                'user_id' => auth()->id(),
                'session_id' => auth()->id() ? null : $this->getSessionId(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'selected_variants' => $selectedVariants,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
            ]);
        }

        $item->load(['product.seller', 'product.category', 'product.brand', 'product.images']);

        return [
            'success' => true,
            'message' => __('Product added to cart.'),
            'item' => $item,
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal(),
        ];
    }

    public function updateQuantity(int $cartItemId, int $quantity): array
    {
        $item = $this->findCartItem($cartItemId);

        if (!$item) {
            return ['success' => false, 'message' => __('Cart item not found.')];
        }

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        $product = $item->product;
        if ($product && $quantity > $product->available_stock) {
            return [
                'success' => false,
                'message' => __('Only :count items available in stock.', ['count' => $product->available_stock]),
            ];
        }

        $item->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => __('Cart updated.'),
            'item' => $item->fresh()->load(['product.seller', 'product.category', 'product.brand', 'product.images']),
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal(),
        ];
    }

    public function removeItem(int $cartItemId): array
    {
        $item = $this->findCartItem($cartItemId);

        if (!$item) {
            return ['success' => false, 'message' => __('Cart item not found.')];
        }

        $item->delete();

        return [
            'success' => true,
            'message' => __('Product removed from cart.'),
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal(),
        ];
    }

    public function clearCart(): array
    {
        $query = CartItem::query();
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }

        $query->delete();

        return [
            'success' => true,
            'message' => __('Cart cleared.'),
            'cart_count' => 0,
            'cart_total' => 0,
        ];
    }

    public function mergeGuestCartToUser(int $userId): void
    {
        $sessionId = $this->getSessionId();

        $guestItems = CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $guestItem) {
            $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity,
                ]);
                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            }
        }
    }

    public function revalidateCart(): array
    {
        $items = $this->getCartItems();
        $removed = 0;
        $updated = 0;

        foreach ($items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active || $product->status !== 'published') {
                $item->delete();
                $removed++;
                continue;
            }

            if (!$product->inStock()) {
                $item->delete();
                $removed++;
                continue;
            }

            if ($item->quantity > $product->available_stock) {
                $item->update(['quantity' => $product->available_stock]);
                $updated++;
            }

            if ($item->price != $product->price) {
                $item->update(['price' => $product->price, 'compare_at_price' => $product->compare_at_price]);
                $updated++;
            }
        }

        return [
            'removed' => $removed,
            'updated' => $updated,
            'cart_count' => $this->getCartCount(),
            'cart_total' => $this->getCartTotal(),
        ];
    }

    public function applyCoupon(string $code): array
    {
        $coupon = DB::table('system_settings')
            ->where('key', 'coupon_' . strtolower($code))
            ->where('value->active', true)
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => __('Invalid coupon code.')];
        }

        $value = json_decode($coupon->value, true);
        $discountType = $value['type'] ?? 'percentage';
        $discountValue = $value['value'] ?? 0;
        $minOrder = $value['min_order'] ?? 0;
        $maxDiscount = $value['max_discount'] ?? null;

        $cartTotal = $this->getCartTotal();

        if ($cartTotal < $minOrder) {
            return [
                'success' => false,
                'message' => __('Minimum order amount for this coupon is :amount.', ['amount' => '$' . number_format($minOrder, 2)]),
            ];
        }

        if ($discountType === 'percentage') {
            $discount = $cartTotal * ($discountValue / 100);
            if ($maxDiscount && $discount > $maxDiscount) {
                $discount = $maxDiscount;
            }
        } else {
            $discount = min($discountValue, $cartTotal);
        }

        Session::put('coupon_code', strtoupper($code));
        Session::put('coupon_discount', $discount);

        return [
            'success' => true,
            'message' => __('Coupon applied successfully!'),
            'discount' => $discount,
            'code' => strtoupper($code),
        ];
    }

    public function removeCoupon(): array
    {
        Session::forget('coupon_code');
        Session::forget('coupon_discount');

        return [
            'success' => true,
            'message' => __('Coupon removed.'),
        ];
    }

    protected function findCartItem(int $id): ?CartItem
    {
        $query = CartItem::query();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', $this->getSessionId())
                  ->whereNull('user_id');
        }

        return $query->find($id);
    }
}
