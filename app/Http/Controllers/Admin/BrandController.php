<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $brands = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $existing = Brand::where('slug', $validated['slug'])->first();
        if ($existing) {
            $validated['slug'] = $validated['slug'] . '-' . time();
        }

        Brand::create($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand "' . $validated['name'] . '" has been created.');
    }

    public function show(Brand $brand)
    {
        return view('admin.brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($validated['name']) && $validated['name'] !== $brand->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $existing = Brand::where('slug', $validated['slug'])->where('id', '!=', $brand->id)->first();
            if ($existing) {
                $validated['slug'] = $validated['slug'] . '-' . time();
            }
        }

        $brand->update($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus(Brand $brand)
    {
        $brand->update([
            'status' => $brand->status === 'active' ? 'inactive' : 'active',
        ]);

        $status = $brand->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', 'Brand ' . $status . ' successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
