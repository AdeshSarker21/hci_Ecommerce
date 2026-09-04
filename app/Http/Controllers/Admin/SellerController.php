<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $query = Seller::with('user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('store_slug', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $sellers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.sellers.index', compact('sellers'));
    }

    public function show(Seller $seller)
    {
        $seller->load(['user', 'staff']);

        return view('admin.sellers.show', compact('seller'));
    }

    public function approve(Seller $seller)
    {
        if (!$seller->isPending()) {
            return back()->with('error', 'Only pending sellers can be approved.');
        }

        $seller->approve();

        $seller->user->roles()->syncWithoutDetaching(
            \App\Models\Role::where('slug', 'seller')->pluck('id')
        );

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been approved.');
    }

    public function reject(Request $request, Seller $seller)
    {
        if (!$seller->isPending()) {
            return back()->with('error', 'Only pending sellers can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $seller->reject($validated['rejection_reason']);

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been rejected.');
    }

    public function suspend(Request $request, Seller $seller)
    {
        if (!$seller->isApproved()) {
            return back()->with('error', 'Only approved sellers can be suspended.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $seller->suspend($validated['rejection_reason']);

        return back()->with('success', 'Seller "' . $seller->store_name . '" has been suspended.');
    }
}
