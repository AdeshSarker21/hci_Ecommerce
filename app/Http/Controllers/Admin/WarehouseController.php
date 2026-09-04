<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with('seller');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('seller', fn ($sq) => $sq->where('store_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $warehouses = $query->latest()->paginate(15)->withQueryString();

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $sellers = Seller::approved()->get();

        return view('admin.warehouses.create', compact('sellers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'seller_id' => ['nullable', 'exists:sellers,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_default'] = $validated['is_default'] ?? false;

        if ($validated['is_default']) {
            Warehouse::where('seller_id', $validated['seller_id'] ?? null)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('seller');

        $products = $warehouse->products()
            ->with(['category', 'brand'])
            ->paginate(15);

        return view('admin.warehouses.show', compact('warehouse', 'products'));
    }

    public function edit(Warehouse $warehouse)
    {
        $sellers = Seller::approved()->get();

        return view('admin.warehouses.edit', compact('warehouse', 'sellers'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:warehouses,code,' . $warehouse->id],
            'seller_id' => ['nullable', 'exists:sellers,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            Warehouse::where('seller_id', $validated['seller_id'] ?? $warehouse->seller_id)
                ->where('is_default', true)
                ->where('id', '!=', $warehouse->id)
                ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }

    public function toggleStatus(Warehouse $warehouse)
    {
        $warehouse->update([
            'status' => $warehouse->status === 'active' ? 'inactive' : 'active',
        ]);

        $status = $warehouse->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', 'Warehouse ' . $status . ' successfully.');
    }
}
