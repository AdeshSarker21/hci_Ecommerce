<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function dashboard()
    {
        $seller = auth()->user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register.show');
        }

        $stats = [
            'status' => $seller->status,
            'is_featured' => $seller->is_featured,
            'staff_count' => $seller->staff()->count(),
        ];

        return view('seller.dashboard', compact('seller', 'stats'));
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
            'store_description' => ['nullable', 'string', 'max:2000'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_city' => ['nullable', 'string', 'max:100'],
            'business_state' => ['nullable', 'string', 'max:100'],
            'business_country' => ['nullable', 'string', 'max:100'],
            'business_postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $seller->update($validated);

        return back()->with('success', 'Store profile updated successfully.');
    }
}
