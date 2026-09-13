<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderService extends Service
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function getCheckoutData(User $user): array
    {
        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();
        $itemsBySeller = $cartService->getCartBySeller();

        $subtotal = (float) $cartItems->sum(fn ($item) => $item->price * $item->quantity);
        $couponDiscount = (float) Session::get('coupon_discount', 0);
        $couponCode = Session::get('coupon_code');

        $shippingBySeller = $itemsBySeller->map(function ($items, $sellerId) use ($subtotal) {
            $sellerSubtotal = (float) $items->sum(fn ($item) => $item->price * $item->quantity);
            $shipping = $sellerSubtotal >= 50 ? 0 : 5.99;

            return [
                'seller_id' => $sellerId,
                'seller' => $items->first()->product->seller,
                'items' => $items,
                'subtotal' => $sellerSubtotal,
                'shipping' => $shipping,
                'item_count' => $items->sum('quantity'),
            ];
        })->values();

        $totalShipping = (float) $shippingBySeller->sum('shipping');
        $total = $subtotal - $couponDiscount + $totalShipping;

        $addresses = $user->addresses()->get();
        $defaultAddress = $addresses->where('is_default', true)->first();

        return [
            'cart_items' => $cartItems,
            'items_by_seller' => $shippingBySeller,
            'subtotal' => $subtotal,
            'coupon_discount' => $couponDiscount,
            'coupon_code' => $couponCode,
            'shipping' => $totalShipping,
            'total' => max(0, $total),
            'addresses' => $addresses,
            'default_address' => $defaultAddress,
            'currency' => 'USD',
        ];
    }

    public function validateCartItems(User $user): array
    {
        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();
        $errors = [];

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active || $product->status !== 'published') {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_name' => $item->product?->name ?? 'Unknown',
                    'message' => __('This product is no longer available.'),
                ];
                continue;
            }

            if (!$product->inStock()) {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_name' => $product->name,
                    'message' => __(':product is out of stock.', ['product' => $product->name]),
                ];
                continue;
            }

            if ($item->quantity > $product->available_stock) {
                $errors[] = [
                    'item_id' => $item->id,
                    'product_name' => $product->name,
                    'message' => __('Only :count :product available in stock.', [
                        'count' => $product->available_stock,
                        'product' => $product->name,
                    ]),
                ];
            }
        }

        return $errors;
    }

    public function placeOrder(
        User $user,
        int $addressId,
        string $paymentMethod,
        ?string $couponCode = null,
        ?string $notes = null
    ): array {
        $address = $user->addresses()->find($addressId);
        if (!$address) {
            return ['success' => false, 'message' => __('Please select a valid shipping address.')];
        }

        $cartService = app(CartService::class);
        $cartItems = $cartService->getCartItems();

        if ($cartItems->isEmpty()) {
            return ['success' => false, 'message' => __('Your cart is empty.')];
        }

        $validationErrors = $this->validateCartItems($user);
        if (!empty($validationErrors)) {
            return [
                'success' => false,
                'message' => __('Some items in your cart are no longer available or out of stock.'),
                'errors' => $validationErrors,
            ];
        }

        $couponDiscount = (float) Session::get('coupon_discount', 0);

        $orders = DB::transaction(function () use ($user, $cartItems, $address, $paymentMethod, $couponDiscount, $couponCode, $notes) {
            $itemsBySeller = $cartItems->groupBy(fn ($item) => $item->product->seller_id);
            $sellerCount = $itemsBySeller->count();
            $createdOrders = [];

            foreach ($itemsBySeller as $sellerId => $items) {
                $sellerSubtotal = (float) $items->sum(fn ($item) => $item->price * $item->quantity);
                $shipping = $sellerSubtotal >= 50 ? 0 : 5.99;

                $sellerDiscount = 0;
                if ($couponDiscount > 0 && $sellerCount > 0) {
                    $sellerDiscount = round($couponDiscount / $sellerCount, 2);
                }

                $orderTotal = max(0, $sellerSubtotal - $sellerDiscount + $shipping);

                $order = Order::create([
                    'seller_id' => $sellerId,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'subtotal' => $sellerSubtotal,
                    'tax' => 0,
                    'shipping_cost' => $shipping,
                    'discount' => $sellerDiscount,
                    'total' => $orderTotal,
                    'currency' => 'USD',
                    'notes' => $notes,
                    'shipping_address' => [
                        'name' => $address->name,
                        'phone' => $address->phone,
                        'email' => $address->email,
                        'address_line_1' => $address->address_line_1,
                        'address_line_2' => $address->address_line_2,
                        'city' => $address->city,
                        'state' => $address->state,
                        'postal_code' => $address->postal_code,
                        'country' => $address->country,
                    ],
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentMethod === 'cod' ? 'pending' : 'pending',
                ]);

                foreach ($items as $item) {
                    $product = $item->product;
                    $quantity = $item->quantity;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'quantity' => $quantity,
                        'unit_price' => $item->price,
                        'total' => $item->price * $quantity,
                    ]);

                    $this->inventoryService->removeStock(
                        $product,
                        $quantity,
                        'sale',
                        "Order #{$order->order_number}",
                        Order::class,
                        $order->id
                    );
                }

                $order->logStatusChange('pending', null, $user->id, 'Order placed.');

                $createdOrders[] = $order;
            }

            return $createdOrders;
        });

        if ($orders) {
            $cartService->clearCart();
            Session::forget('coupon_code');
            Session::forget('coupon_discount');
        }

        return [
            'success' => true,
            'message' => __('Order placed successfully!'),
            'orders' => $orders,
            'order_ids' => collect($orders)->pluck('id')->toArray(),
        ];
    }
}
