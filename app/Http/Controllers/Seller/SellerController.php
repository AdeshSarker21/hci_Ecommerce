<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function dashboard()
    {
        $seller = auth()->user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register.show');
        }

        // If approved, redirect to analytics dashboard
        if ($seller->isApproved()) {
            return redirect()->route('seller.analytics');
        }

        $stats = [
            'status' => $seller->status,
            'is_featured' => $seller->is_featured,
            'staff_count' => $seller->staff()->count(),
            'total_products' => $seller->products()->count(),
            'published_products' => $seller->products()->where('status', 'published')->count(),
            'draft_products' => $seller->products()->where('status', 'draft')->count(),
            'pending_products' => $seller->products()->where('status', 'pending_review')->count(),
            'store_url' => null,
        ];

        $recentProducts = $seller->products()
            ->with(['category', 'images' => function ($q) {
                $q->where('is_featured', true)->limit(1);
            }])
            ->latest()
            ->limit(5)
            ->get();

        return view('seller.dashboard', compact('seller', 'stats', 'recentProducts'));
    }

    public function showRegisterForm()
    {
        if (auth()->user()->seller) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.register');
    }

    public function register(Request $request)
    {
        if (auth()->user()->seller) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:2000'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_city' => ['nullable', 'string', 'max:100'],
            'business_state' => ['nullable', 'string', 'max:100'],
            'business_country' => ['nullable', 'string', 'max:100'],
            'business_postal_code' => ['nullable', 'string', 'max:20'],
            'business_registration_number' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $validated['contact_email'] = auth()->user()->email;

        $seller = Seller::create($validated);

        $sellerRole = \App\Models\Role::where('slug', 'seller')->first();
        if ($sellerRole) {
            auth()->user()->roles()->syncWithoutDetaching([$sellerRole->id]);
        }

        return redirect()->route('seller.dashboard')
            ->with('success', 'Your seller application has been submitted! It will be reviewed by our team shortly.');
    }

    public function editProfile()
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.profile', compact('seller'));
    }

    public function updateProfile(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'store_name' => ['sometimes', 'string', 'max:255'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'store_tagline_bn' => ['nullable', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:2000'],
            'store_description_bn' => ['nullable', 'string', 'max:2000'],
            'store_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'store_banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_city' => ['nullable', 'string', 'max:100'],
            'business_state' => ['nullable', 'string', 'max:100'],
            'business_country' => ['nullable', 'string', 'max:100'],
            'business_postal_code' => ['nullable', 'string', 'max:20'],
            'about_us' => ['nullable', 'string', 'max:5000'],
            'about_us_bn' => ['nullable', 'string', 'max:5000'],
            'shipping_policy' => ['nullable', 'string', 'max:5000'],
            'shipping_policy_bn' => ['nullable', 'string', 'max:5000'],
            'return_policy' => ['nullable', 'string', 'max:5000'],
            'return_policy_bn' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($request->hasFile('store_logo')) {
            if ($seller->store_logo && \Storage::disk('public')->exists($seller->store_logo)) {
                \Storage::disk('public')->delete($seller->store_logo);
            }
            $validated['store_logo'] = $request->file('store_logo')->store('seller-logos', 'public');
        }

        if ($request->hasFile('store_banner')) {
            if ($seller->store_banner && \Storage::disk('public')->exists($seller->store_banner)) {
                \Storage::disk('public')->delete($seller->store_banner);
            }
            $validated['store_banner'] = $request->file('store_banner')->store('seller-banners', 'public');
        }

        $seller->update($validated);

        return back()->with('success', 'Store profile updated successfully.');
    }
}
