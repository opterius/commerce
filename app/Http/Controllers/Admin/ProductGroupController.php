<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductGroup;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function index()
    {
        $groups = ProductGroup::withCount('products')->orderBy('sort_order')->get();
        return view('admin.products.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.products.groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_groups'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $validated['is_visible'] = $request->boolean('is_visible', true);
        $group = ProductGroup::create($validated);

        ActivityLogger::log('product_group.created', 'product_group', $group->id, $group->name);

        return redirect()->route('admin.product-groups.index')
            ->with('success', __('products.group_created'));
    }

    public function edit(ProductGroup $productGroup)
    {
        return view('admin.products.groups.edit', ['group' => $productGroup]);
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:product_groups,slug,' . $productGroup->id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
        ]);

        $validated['is_visible'] = $request->boolean('is_visible', true);
        $productGroup->update($validated);

        ActivityLogger::log('product_group.updated', 'product_group', $productGroup->id, $productGroup->name);

        return redirect()->route('admin.product-groups.index')
            ->with('success', __('products.group_updated'));
    }

    public function destroy(Request $request, ProductGroup $productGroup)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        ActivityLogger::log('product_group.deleted', 'product_group', $productGroup->id, $productGroup->name);
        $productGroup->delete();

        return redirect()->route('admin.product-groups.index')
            ->with('success', __('products.group_deleted'));
    }
}
