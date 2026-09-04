<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Notifications\ProductStatusChanged;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand'])
            ->whereIn('status', ['pending_review', 'approved', 'published', 'rejected']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        $sellers = Seller::approved()->get();
        $categories = Category::active()->ordered()->get();

        $counts = [
            'pending_review' => Product::where('status', 'pending_review')->count(),
            'approved' => Product::where('status', 'approved')->count(),
            'published' => Product::where('status', 'published')->count(),
            'rejected' => Product::where('status', 'rejected')->count(),
        ];

        return view('admin.review.index', compact('products', 'sellers', 'categories', 'counts'));
    }

    public function show(Product $product)
    {
        $product->load(['seller', 'category', 'brand', 'images', 'attributeValues.attribute', 'moderations.reviewer']);

        return view('admin.review.show', compact('product'));
    }

    public function approve(Request $request, Product $product)
    {
        $previousStatus = $product->status;

        if (!in_array($previousStatus, ['pending_review', 'rejected'])) {
            return back()->with('error', 'Only pending review or rejected products can be approved.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $product->approve();
        $product->logModeration('approve', $validated['reason'] ?? null, $previousStatus, 'approved');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'approved', $validated['reason'] ?? null)
        );

        return back()->with('success', 'Product "' . $product->name . '" has been approved.');
    }

    public function reject(Request $request, Product $product)
    {
        $previousStatus = $product->status;

        if (!in_array($previousStatus, ['pending_review', 'approved'])) {
            return back()->with('error', 'Only pending review or approved products can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $product->reject($validated['rejection_reason']);
        $product->logModeration('reject', $validated['rejection_reason'], $previousStatus, 'rejected');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'rejected', $validated['rejection_reason'])
        );

        return back()->with('success', 'Product "' . $product->name . '" has been rejected.');
    }

    public function publish(Product $product)
    {
        $previousStatus = $product->status;

        if ($previousStatus !== 'approved') {
            return back()->with('error', 'Only approved products can be published.');
        }

        $product->publish();
        $product->logModeration('publish', null, $previousStatus, 'published');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'published')
        );

        return back()->with('success', 'Product "' . $product->name . '" has been published.');
    }

    public function unpublish(Product $product)
    {
        $previousStatus = $product->status;

        if ($previousStatus !== 'published') {
            return back()->with('error', 'Only published products can be unpublished.');
        }

        $product->unpublish();
        $product->logModeration('unpublish', null, $previousStatus, 'approved');

        $product->seller->user->notify(
            new ProductStatusChanged($product, $previousStatus, 'approved')
        );

        return back()->with('success', 'Product "' . $product->name . '" has been unpublished.');
    }
}
