<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['attributes' => function ($q) {
            $q->withPivot('sort_order', 'is_required');
        }])->ordered();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%");
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('admin.category-attributes.index', compact('categories'));
    }

    public function edit(Category $category)
    {
        $category->load(['attributes' => function ($q) {
            $q->withPivot('sort_order', 'is_required');
        }]);

        $allAttributes = Attribute::active()->ordered()->get();

        return view('admin.category-attributes.edit', compact('category', 'allAttributes'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['integer', 'exists:attributes,id'],
            'sort_order' => ['nullable', 'array'],
            'sort_order.*' => ['integer', 'min:0'],
            'is_required' => ['nullable', 'array'],
        ]);

        $attributeIds = $validated['attributes'] ?? [];
        $sortOrders = $validated['sort_order'] ?? [];
        $isRequired = $validated['is_required'] ?? [];

        $existing = $category->attributes()->pluck('category_attributes.attribute_id')->toArray();

        $toRemove = array_diff($existing, $attributeIds);
        if (!empty($toRemove)) {
            $category->attributes()->detach($toRemove);
        }

        foreach ($attributeIds as $index => $attributeId) {
            $category->attributes()->syncWithoutDetaching([
                $attributeId => [
                    'sort_order' => $sortOrders[$attributeId] ?? $index,
                    'is_required' => isset($isRequired[$attributeId]),
                    'updated_at' => now(),
                ],
            ]);
        }

        return back()->with('success', 'Category attributes updated successfully.');
    }

    public function getAttributes(Category $category)
    {
        $attributes = $category->attributes()
            ->with(['values' => function ($q) {
                $q->orderBy('sort_order');
            }])
            ->orderByPivot('sort_order')
            ->get()
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'name_bn' => $attr->name_bn,
                    'type' => $attr->type,
                    'is_required' => $attr->pivot->is_required,
                    'values' => $attr->values->map(fn($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                        'value_bn' => $v->value_bn,
                    ]),
                ];
            });

        return response()->json($attributes);
    }
}
