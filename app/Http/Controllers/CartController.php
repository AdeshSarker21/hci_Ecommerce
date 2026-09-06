<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function index(): View
    {
        $cartItems = $this->cart->getCartItems();
        $subtotal = $this->cart->getCartTotal();
        $discount = $this->cart->getCartDiscount();
        $shipping = $this->cart->getCartShipping();
        $grandTotal = $this->cart->getCartGrandTotal();
        $itemsBySeller = $this->cart->getCartBySeller();
        $cartCount = $this->cart->getCartCount();

        $recommendedProducts = Product::where('is_active', true)
            ->where('status', 'published')
            ->where('stock_quantity', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get(['id', 'name', 'slug', 'price', 'primary_image']);

        return view('cart.index', compact(
            'cartItems',
            'subtotal',
            'discount',
            'shipping',
            'grandTotal',
            'itemsBySeller',
            'cartCount',
            'recommendedProducts'
        ));
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:100',
            'selected_variants' => 'nullable|array',
        ]);

        $result = $this->cart->addItem(
            $request->product_id,
            $request->quantity ?? 1,
            $request->selected_variants
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $result = $this->cart->updateQuantity($id, $request->quantity);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function remove(int $id): JsonResponse
    {
        $result = $this->cart->removeItem($id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function clear(): JsonResponse
    {
        $result = $this->cart->clearCart();

        return response()->json($result);
    }

    public function summary(): JsonResponse
    {
        $cartItems = $this->cart->getCartItems();

        return response()->json([
            'success' => true,
            'cart_count' => $this->cart->getCartCount(),
            'cart_total' => $this->cart->getCartTotal(),
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'image' => $item->product->primary_image ? asset('storage/' . $item->product->primary_image) : null,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'seller' => $item->product->seller?->store_name,
                    'in_stock' => $item->product?->inStock(),
                    'max_stock' => $item->product?->available_stock ?? 0,
                ];
            }),
        ]);
    }

    public function revalidate(): JsonResponse
    {
        $result = $this->cart->revalidateCart();

        return response()->json(array_merge($result, [
            'success' => true,
        ]));
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $result = $this->cart->applyCoupon($request->code);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function removeCoupon(): JsonResponse
    {
        $result = $this->cart->removeCoupon();

        return response()->json($result);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'count' => $this->cart->getCartCount(),
        ]);
    }
}
