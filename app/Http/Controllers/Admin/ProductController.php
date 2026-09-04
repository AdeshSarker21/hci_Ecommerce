<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'brand']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn ($sq) => $sq->where('store_name', 'like', "%{$search}%"));
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        if ($sellerId = $request->input('seller_id')) {
            $query->where('seller_id', $sellerId);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load(['seller', 'category', 'brand']);

        return view('admin.products.show', compact('product'));
    }

    public function approve(Product $product)
    {
        if (!in_array($product->status, ['pending_review', 'rejected'])) {
            return back()->with('error', 'Only pending review or rejected products can be approved.');
        }

        $product->approve();

        return back()->with('success', 'Product "' . $product->name . '" has been approved.');
    }

    public function reject(Request $request, Product $product)
    {
        if (!in_array($product->status, ['pending_review', 'approved'])) {
            return back()->with('error', 'Only pending review or approved products can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $product->reject($validated['rejection_reason']);

        return back()->with('success', 'Product "' . $product->name . '" has been rejected.');
    }

    public function publish(Product $product)
    {
        if ($product->status !== 'approved') {
            return back()->with('error', 'Only approved products can be published.');
        }

        $product->publish();

        return back()->with('success', 'Product "' . $product->name . '" has been published.');
    }

    public function unpublish(Product $product)
    {
        if ($product->status !== 'published') {
            return back()->with('error', 'Only published products can be unpublished.');
        }

        $product->unpublish();

        return back()->with('success', 'Product "' . $product->name . '" has been unpublished.');
    }
}
