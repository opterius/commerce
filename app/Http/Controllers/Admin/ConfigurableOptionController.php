<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOption;
use App\Models\ConfigurableOptionGroup;
use App\Models\ConfigurableOptionValue;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ConfigurableOptionController extends Controller
{
    public function index()
    {
        $groups = ConfigurableOptionGroup::withCount('options', 'products')->orderBy('name')->get();
        return view('admin.products.options.index', compact('groups'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('admin.products.options.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
        ]);

        $group = ConfigurableOptionGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->has('products')) {
            $group->products()->sync($request->input('products'));
        }

        ActivityLogger::log('option_group.created', 'configurable_option_group', $group->id, $group->name);

        return redirect()->route('admin.configurable-options.show', $group)
            ->with('success', __('products.option_group_created'));
    }

    public function show(ConfigurableOptionGroup $configurableOption)
    {
        $group = $configurableOption;
        $group->load('options.values', 'products');
        $allProducts = Product::orderBy('name')->get();

        return view('admin.products.options.show', compact('group', 'allProducts'));
    }

    public function update(Request $request, ConfigurableOptionGroup $configurableOption)
    {
        $group = $configurableOption;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'products' => ['nullable', 'array'],
            'products.*' => ['exists:products,id'],
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $group->products()->sync($request->input('products', []));

        ActivityLogger::log('option_group.updated', 'configurable_option_group', $group->id, $group->name);

        return back()->with('success', __('products.option_group_updated'));
    }

    public function destroy(Request $request, ConfigurableOptionGroup $configurableOption)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        ActivityLogger::log('option_group.deleted', 'configurable_option_group', $configurableOption->id, $configurableOption->name);
        $configurableOption->delete();

        return redirect()->route('admin.configurable-options.index')
            ->with('success', __('products.option_group_deleted'));
    }

    // ── Options within a group ──────────────────────────────────────────────

    public function storeOption(Request $request, ConfigurableOptionGroup $group)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'option_type' => ['required', 'in:dropdown,radio,checkbox,quantity'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $group->options()->create($validated);

        return back()->with('success', __('products.option_created'));
    }

    public function destroyOption(ConfigurableOptionGroup $group, ConfigurableOption $option)
    {
        if ($option->group_id !== $group->id) abort(404);
        $option->delete();
        return back()->with('success', __('products.option_deleted'));
    }

    // ── Values within an option ─────────────────────────────────────────────

    public function storeValue(Request $request, ConfigurableOption $option)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $option->values()->create($validated);

        return back()->with('success', __('products.option_updated'));
    }

    public function destroyValue(ConfigurableOption $option, ConfigurableOptionValue $value)
    {
        if ($value->option_id !== $option->id) abort(404);
        $value->delete();
        return back()->with('success', __('products.option_updated'));
    }
}
