<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class SellerWarehouseController extends Controller
{
    public function index(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $query = Warehouse::where('seller_id', $seller->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->latest()->paginate(15)->withQueryString();

        return view('seller.warehouses.index', compact('seller', 'warehouses'));
    }

    public function create()
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.warehouses.create', compact('seller'));
    }

    public function store(Request $request)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved()) {
            return redirect()->route('seller.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['seller_id'] = $seller->id;
        $validated['status'] = 'active';
        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            Warehouse::where('seller_id', $seller->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return redirect()->route('seller.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $warehouse->seller_id !== $seller->id) {
            abort(403);
        }

        $products = $warehouse->products()
            ->with(['category', 'brand'])
            ->paginate(15);

        return view('seller.warehouses.show', compact('seller', 'warehouse', 'products'));
    }

    public function edit(Warehouse $warehouse)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $warehouse->seller_id !== $seller->id) {
            abort(403);
        }

        return view('seller.warehouses.edit', compact('seller', 'warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $warehouse->seller_id !== $seller->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:warehouses,code,' . $warehouse->id],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            Warehouse::where('seller_id', $seller->id)
                ->where('is_default', true)
                ->where('id', '!=', $warehouse->id)
                ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('seller.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $seller = auth()->user()->seller;

        if (!$seller || !$seller->isApproved() || $warehouse->seller_id !== $seller->id) {
            abort(403);
        }

        $warehouse->delete();

        return redirect()->route('seller.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}
