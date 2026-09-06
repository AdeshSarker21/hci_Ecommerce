<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorefrontReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if (!$product->isPublished() || !$product->is_active) {
            return back()->with('error', __('This product is not available for review.'));
        }

        $existingReview = ProductReview::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', __('You have already reviewed this product.'));
        }

        $isVerified = $product->orders()
            ->where('user_id', Auth::id())
            ->where('order_items.status', 'delivered')
            ->exists();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $isVerified,
            'is_approved' => false,
        ]);

        return back()->with('success', __('Your review has been submitted and is pending approval.'));
    }
}
