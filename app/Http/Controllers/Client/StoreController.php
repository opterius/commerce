<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Product catalog — all active product groups and their products,
     * with pricing resolved in the client's currency.
     */
    public function index()
    {
        $client       = auth('client')->user();
        $currencyCode = $client->currency_code ?: (Currency::getDefault()?->code ?? 'USD');
        $currency     = Currency::where('code', $currencyCode)->first() ?? Currency::getDefault();

        $groups = ProductGroup::with([
            'products' => fn($q) => $q->where('status', 'active')->orderBy('sort_order'),
            'products.pricing',
        ])
        ->whereHas('products', fn($q) => $q->where('status', 'active'))
        ->orderBy('sort_order')
        ->get();

        return view('client.store.index', compact('groups', 'currency', 'currencyCode'));
    }

    /**
     * Single product order form — billing cycle selector, configurable options, promo code.
     */
    public function show(Product $product)
    {
        abort_if($product->status !== 'active', 404);

        $client       = auth('client')->user();
        $currencyCode = $client->currency_code ?: (Currency::getDefault()?->code ?? 'USD');
        $currency     = Currency::where('code', $currencyCode)->first() ?? Currency::getDefault();

        $product->load(['pricing', 'configurableOptionGroups.options.values.pricing']);

        // Build available cycles — only show cycles that have a price in client's currency
        // or in the default currency as fallback.
        $defaultCode = Currency::getDefault()?->code ?? 'USD';
        $cycles      = $product->pricing
            ->filter(fn($p) => $p->currency_code === $currencyCode || $p->currency_code === $defaultCode)
            ->sortBy(fn($p) => $p->currency_code === $currencyCode ? 0 : 1)  // client currency first
            ->unique('billing_cycle')
            ->keyBy('billing_cycle');

        abort_if($cycles->isEmpty(), 404, 'Product not available in your currency.');

        return view('client.store.order', compact('product', 'cycles', 'currency', 'currencyCode'));
    }

    /**
     * Create order from submitted form.
     */
    public function order(Request $request, Product $product)
    {
        abort_if($product->status !== 'active', 404);

        $client = auth('client')->user();

        $request->validate([
            'billing_cycle'   => ['required', 'string'],
            'config_options'  => ['nullable', 'array'],
            'config_options.*'=> ['nullable', 'integer'],
            'promo_code'      => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $selectedValues = array_values(array_filter($request->input('config_options', [])));

            $order = $this->orderService->create(
                client:           $client,
                product:          $product,
                cycle:            $request->input('billing_cycle'),
                selectedValueIds: $selectedValues,
                promoCode:        $request->input('promo_code'),
            );

            return redirect()
                ->route('client.invoices.show', $order->invoice_id)
                ->with('success', __('store.order_placed'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX endpoint — validate a promo code and return the discount amount.
     */
    public function promoCheck(Request $request): JsonResponse
    {
        $request->validate([
            'code'          => ['required', 'string', 'max:50'],
            'product_id'    => ['required', 'integer'],
            'currency_code' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'string'],
        ]);

        $product = Product::find($request->integer('product_id'));

        if (! $product || $product->status !== 'active') {
            return response()->json(['valid' => false, 'message' => __('store.promo_check_failed')]);
        }

        $product->load('pricing');

        $currencyCode = $request->input('currency_code');
        $cycle        = $request->input('billing_cycle');

        $pricing = $product->getPriceForCycle($currencyCode, $cycle);

        if (! $pricing) {
            $default = Currency::getDefault();
            $pricing = $default ? $product->getPriceForCycle($default->code, $cycle) : null;
        }

        if (! $pricing) {
            return response()->json(['valid' => false, 'message' => __('store.promo_check_failed')]);
        }

        $result = $this->orderService->validatePromoCode(
            $request->input('code'),
            $product,
            $currencyCode,
            $cycle,
            $pricing->price,
        );

        if (! $result) {
            return response()->json(['valid' => false, 'message' => __('store.promo_invalid')]);
        }

        return response()->json([
            'valid'    => true,
            'discount' => $result['discount'],
            'message'  => __('store.promo_applied', ['code' => $result['code']]),
        ]);
    }
}
