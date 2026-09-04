<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerStaff;
use App\Models\User;
use Illuminate\Http\Request;

class SellerStaffController extends Controller
{
    public function index()
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $staff = $seller->staffDetails()->with('user')->latest()->get();

        return view('seller.staff.index', compact('seller', 'staff'));
    }

    public function create()
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.staff.create', compact('seller'));
    }

    public function store(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'can_manage_products' => ['nullable', 'boolean'],
            'can_manage_orders' => ['nullable', 'boolean'],
            'can_manage_settings' => ['nullable', 'boolean'],
            'can_view_reports' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser && $seller->staffDetails()->where('user_id', $existingUser->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a staff member.'])->withInput();
        }

        $staff = $seller->staffDetails()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_id' => $existingUser?->id,
            'role' => $validated['role'],
            'can_manage_products' => $validated['can_manage_products'] ?? false,
            'can_manage_orders' => $validated['can_manage_orders'] ?? false,
            'can_manage_settings' => $validated['can_manage_settings'] ?? false,
            'can_view_reports' => $validated['can_view_reports'] ?? false,
            'notes' => $validated['notes'] ?? null,
            'invited_at' => now(),
            'is_active' => true,
        ]);

        if ($existingUser) {
            $staffRole = \App\Models\Role::where('slug', 'seller-staff')->first();
            if ($staffRole) {
                $existingUser->roles()->syncWithoutDetaching([$staffRole->id]);
            }
        }

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member "' . $validated['name'] . '" has been invited.');
    }

    public function show(SellerStaff $staff)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($staff->seller_id !== $seller->id) {
            abort(403);
        }

        $staff->load('user');

        return view('seller.staff.show', compact('seller', 'staff'));
    }

    public function edit(SellerStaff $staff)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($staff->seller_id !== $seller->id) {
            abort(403);
        }

        return view('seller.staff.edit', compact('seller', 'staff'));
    }

    public function update(Request $request, SellerStaff $staff)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($staff->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'max:50'],
            'can_manage_products' => ['nullable', 'boolean'],
            'can_manage_orders' => ['nullable', 'boolean'],
            'can_manage_settings' => ['nullable', 'boolean'],
            'can_view_reports' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $staff->update($validated);

        return back()->with('success', 'Staff member updated successfully.');
    }

    public function toggleStatus(SellerStaff $staff)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($staff->seller_id !== $seller->id) {
            abort(403);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        $status = $staff->is_active ? 'activated' : 'deactivated';

        return back()->with('success', 'Staff member ' . $status . ' successfully.');
    }

    public function destroy(SellerStaff $staff)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        if ($staff->seller_id !== $seller->id) {
            abort(403);
        }

        $staff->delete();

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}
