<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigurableOptionGroup;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductPricing;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const BILLING_CYCLES = [
        'monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'one_time',
    ];

    public function index(Request $request)
    {
        $query = Product::with('group');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($groupId = $request->input('group')) {
            $query->where('product_group_id', $groupId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('sort_order')->paginate(config('commerce.pagination', 25));
        $groups = ProductGroup::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'groups'));
    }

    public function create()
    {
        $groups = ProductGroup::orderBy('sort_order')->get();
        $currencies = Currency::where('is_active', true)->get();
        $optionGroups = ConfigurableOptionGroup::orderBy('name')->get();

        return view('admin.products.create', [
            'groups' => $groups,
            'currencies' => $currencies,
            'optionGroups' => $optionGroups,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products'],
            'product_group_id' => ['required', 'exists:product_groups,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:hosting,other'],
            'status' => ['required', 'in:active,hidden,retired'],
            'provisioning_module' => ['nullable', 'string'],
            'stock_control' => ['nullable', 'boolean'],
            'qty_in_stock' => ['nullable', 'integer', 'min:0'],
            'require_domain' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['stock_control'] = $request->boolean('stock_control');
        $validated['require_domain'] = $request->boolean('require_domain');

        $product = Product::create($validated);

        // Save pricing
        $this->savePricing($product, $request);

        // Sync configurable option groups
        if ($request->has('option_groups')) {
            $product->configurableOptionGroups()->sync($request->input('option_groups', []));
        }

        ActivityLogger::log('product.created', 'product', $product->id, $product->name);

        return redirect()->route('admin.products.show', $product)
            ->with('success', __('products.product_created'));
    }

    public function show(Product $product)
    {
        $product->load('group', 'pricing', 'configurableOptionGroups.options.values');
        $currencies = Currency::where('is_active', true)->get();

        return view('admin.products.show', [
            'product' => $product,
            'currencies' => $currencies,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function edit(Product $product)
    {
        $product->load('pricing', 'configurableOptionGroups');
        $groups = ProductGroup::orderBy('sort_order')->get();
        $currencies = Currency::where('is_active', true)->get();
        $optionGroups = ConfigurableOptionGroup::orderBy('name')->get();

        return view('admin.products.edit', [
            'product' => $product,
            'groups' => $groups,
            'currencies' => $currencies,
            'optionGroups' => $optionGroups,
            'billingCycles' => self::BILLING_CYCLES,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'product_group_id' => ['required', 'exists:product_groups,id'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:hosting,other'],
            'status' => ['required', 'in:active,hidden,retired'],
            'provisioning_module' => ['nullable', 'string'],
            'stock_control' => ['nullable', 'boolean'],
            'qty_in_stock' => ['nullable', 'integer', 'min:0'],
            'require_domain' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['stock_control'] = $request->boolean('stock_control');
        $validated['require_domain'] = $request->boolean('require_domain');

        $product->update($validated);

        $this->savePricing($product, $request);

        $product->configurableOptionGroups()->sync($request->input('option_groups', []));

        ActivityLogger::log('product.updated', 'product', $product->id, $product->name);

        return redirect()->route('admin.products.show', $product)
            ->with('success', __('products.product_updated'));
    }

    public function destroy(Request $request, Product $product)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        ActivityLogger::log('product.deleted', 'product', $product->id, $product->name);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', __('products.product_deleted'));
    }

    private function savePricing(Product $product, Request $request): void
    {
        $currencies = Currency::where('is_active', true)->get();

        foreach ($currencies as $currency) {
            foreach (self::BILLING_CYCLES as $cycle) {
                $priceKey = "pricing.{$currency->code}.{$cycle}.price";
                $setupKey = "pricing.{$currency->code}.{$cycle}.setup_fee";

                $priceInput = $request->input($priceKey);

                if ($priceInput === null || $priceInput === '') {
                    // Remove pricing for this combination if blank
                    ProductPricing::where('product_id', $product->id)
                        ->where('currency_code', $currency->code)
                        ->where('billing_cycle', $cycle)
                        ->delete();
                    continue;
                }

                $priceInCents = (int) round(floatval($priceInput) * pow(10, $currency->decimal_places));
                $setupInCents = (int) round(floatval($request->input($setupKey, 0)) * pow(10, $currency->decimal_places));

                ProductPricing::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'currency_code' => $currency->code,
                        'billing_cycle' => $cycle,
                    ],
                    [
                        'price' => $priceInCents,
                        'setup_fee' => $setupInCents,
                    ]
                );
            }
        }
    }
}
