<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = Attribute::with('values');

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

        $attributes = $query->ordered()->paginate(15)->withQueryString();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:text,textarea,number,select,radio,checkbox,color,date'],
            'is_required' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_variant' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
            'values.*.value_bn' => ['nullable', 'string', 'max:255'],
            'values.*.color_code' => ['nullable', 'string', 'max:20'],
        ]);

        $slug = Str::slug($validated['name']);
        $existing = Attribute::where('slug', $slug)->first();
        if ($existing) {
            $slug = $slug . '-' . time();
        }

        $attribute = Attribute::create([
            'name' => $validated['name'],
            'name_bn' => $validated['name_bn'] ?? null,
            'slug' => $slug,
            'type' => $validated['type'],
            'is_required' => $validated['is_required'] ?? false,
            'is_filterable' => $validated['is_filterable'] ?? false,
            'is_variant' => $validated['is_variant'] ?? false,
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    $valueSlug = Str::slug($valueData['value']);
                    $existingValue = AttributeValue::where('attribute_id', $attribute->id)->where('slug', $valueSlug)->first();
                    if ($existingValue) {
                        $valueSlug = $valueSlug . '-' . time();
                    }

                    $attribute->values()->create([
                        'value' => $valueData['value'],
                        'value_bn' => $valueData['value_bn'] ?? null,
                        'slug' => $valueSlug,
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute "' . $validated['name'] . '" has been created.');
    }

    public function show(Attribute $attribute)
    {
        $attribute->load('values');

        return view('admin.attributes.show', compact('attribute'));
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:text,textarea,number,select,radio,checkbox,color,date'],
            'is_required' => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_variant' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'values' => ['nullable', 'array'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
            'values.*.value_bn' => ['nullable', 'string', 'max:255'],
            'values.*.color_code' => ['nullable', 'string', 'max:20'],
        ]);

        if (isset($validated['name']) && $validated['name'] !== $attribute->name) {
            $slug = Str::slug($validated['name']);
            $existing = Attribute::where('slug', $slug)->where('id', '!=', $attribute->id)->first();
            if ($existing) {
                $slug = $slug . '-' . time();
            }
            $validated['slug'] = $slug;
        }

        $attribute->update(collect($validated)->except('values')->toArray());

        if (isset($validated['values'])) {
            $existingIds = collect($validated['values'])->pluck('id')->filter()->toArray();
            $attribute->values()->whereNotIn('id', $existingIds)->delete();

            foreach ($validated['values'] as $index => $valueData) {
                if (empty($valueData['value'])) {
                    continue;
                }

                $valueSlug = Str::slug($valueData['value']);
                $existingValue = AttributeValue::where('attribute_id', $attribute->id)->where('slug', $valueSlug)->where('id', '!=', $valueData['id'] ?? 0)->first();
                if ($existingValue) {
                    $valueSlug = $valueSlug . '-' . time();
                }

                if (!empty($valueData['id'])) {
                    $attribute->values()->where('id', $valueData['id'])->update([
                        'value' => $valueData['value'],
                        'value_bn' => $valueData['value_bn'] ?? null,
                        'slug' => $valueSlug,
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                } else {
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                        'value_bn' => $valueData['value_bn'] ?? null,
                        'slug' => $valueSlug,
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function toggleStatus(Attribute $attribute)
    {
        $attribute->update([
            'status' => $attribute->status === 'active' ? 'inactive' : 'active',
        ]);

        $status = $attribute->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', 'Attribute ' . $status . ' successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    public function destroyValue(AttributeValue $value)
    {
        $value->delete();

        return back()->with('success', 'Attribute value deleted successfully.');
    }
}
