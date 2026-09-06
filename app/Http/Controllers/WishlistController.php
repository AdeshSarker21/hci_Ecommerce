<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\RecentlyViewedService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlist,
        protected CartService $cart,
        protected RecentlyViewedService $recentlyViewed
    ) {}

    public function index(): View
    {
        $wishlistItems = $this->wishlist->getWishlistItems();
        $wishlistCount = $this->wishlist->getWishlistCount();

        return view('wishlist.index', compact('wishlistItems', 'wishlistCount'));
    }

    public function toggle(int $id): JsonResponse
    {
        $result = $this->wishlist->toggle($id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function remove(int $id): JsonResponse
    {
        $result = $this->wishlist->remove($id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function clear(): JsonResponse
    {
        $result = $this->wishlist->clear();

        return response()->json($result);
    }

    public function moveToCart(int $id): JsonResponse
    {
        $result = $this->wishlist->moveToCart($id, $this->cart);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'count' => $this->wishlist->getWishlistCount(),
        ]);
    }

    public function check(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'is_wishlisted' => $this->wishlist->isInWishlist($id),
        ]);
    }

    public function recentlyViewed()
    {
        $items = $this->recentlyViewed->getItems(8);

        return response()->json([
            'success' => true,
            'items' => $items->map(fn ($product) => [
                'id' => $product->id,
                'name' => app()->getLocale() === 'bn' && $product->name_bn ? $product->name_bn : $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'image' => $product->primary_image ? asset('storage/' . $product->primary_image) : null,
                'in_stock' => $product->inStock(),
                'seller' => $product->seller?->store_name,
                'brand' => app()->getLocale() === 'bn' && $product->brand?->name_bn ? $product->brand->name_bn : $product->brand?->name,
                'discount_percentage' => $product->discount_percentage,
            ]),
        ]);
    }
}
