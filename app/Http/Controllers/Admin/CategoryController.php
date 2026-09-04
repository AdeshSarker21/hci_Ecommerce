<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent');

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

        if ($parentId = $request->input('parent_id')) {
            $query->where('parent_id', $parentId);
        }

        $categories = $query->ordered()->paginate(15)->withQueryString();
        $rootCategories = Category::roots()->ordered()->get();

        return view('admin.categories.index', compact('categories', 'rootCategories'));
    }

    public function create()
    {
        $parentCategories = Category::roots()->ordered()->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);

        $existing = Category::where('slug', $validated['slug'])->first();
        if ($existing) {
            $validated['slug'] = $validated['slug'] . '-' . time();
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category "' . $validated['name'] . '" has been created.');
    }

    public function show(Category $category)
    {
        $category->load(['parent', 'children']);
        $childrenCount = $category->children()->count();

        return view('admin.categories.show', compact('category', 'childrenCount'));
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::roots()
            ->where('id', '!=', $category->id)
            ->ordered()
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($validated['name']) && $validated['name'] !== $category->name) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            $existing = Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->first();
            if ($existing) {
                $validated['slug'] = $validated['slug'] . '-' . time();
            }
        }

        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            unset($validated['parent_id']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update([
            'status' => $category->status === 'active' ? 'inactive' : 'active',
        ]);

        $status = $category->status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', 'Category ' . $status . ' successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully. Child categories have been reassigned.');
    }
}
