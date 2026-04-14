<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientGroup;
use App\Models\ClientGroupPricing;
use App\Models\Currency;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ClientGroupController extends Controller
{
    public function index()
    {
        $groups = ClientGroup::withCount('clients')->orderBy('name')->get();

        return view('admin.client-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.client-groups.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['discount_percent'] = $this->normaliseDiscount($request);

        $group = ClientGroup::create($data);
        ActivityLogger::log('client_group.created', 'client_group', $group->id, $group->name, null);

        return redirect()->route('admin.client-groups.edit', $group)
            ->with('success', __('clients.group_created'));
    }

    public function edit(ClientGroup $clientGroup)
    {
        $clientGroup->load('pricing.product');

        $products   = Product::orderBy('name')->get();
        $currencies = Currency::orderBy('is_default', 'desc')->orderBy('code')->get();
        $cycles     = ['monthly', 'quarterly', 'semi_annual', 'annual', 'biennial', 'triennial'];

        return view('admin.client-groups.edit', [
            'group'      => $clientGroup,
            'products'   => $products,
            'currencies' => $currencies,
            'cycles'     => $cycles,
        ]);
    }

    public function update(Request $request, ClientGroup $clientGroup)
    {
        $data = $this->validateData($request);
        $data['discount_percent'] = $this->normaliseDiscount($request);

        $clientGroup->update($data);
        ActivityLogger::log('client_group.updated', 'client_group', $clientGroup->id, $clientGroup->name, null);

        return redirect()->route('admin.client-groups.edit', $clientGroup)
            ->with('success', __('clients.group_updated'));
    }

    public function destroy(Request $request, ClientGroup $clientGroup)
    {
        $request->validate(['password' => ['required', 'current_password:staff']]);

        $name = $clientGroup->name;
        $clientGroup->delete();
        ActivityLogger::log('client_group.deleted', 'client_group', $clientGroup->id, $name, null);

        return redirect()->route('admin.client-groups.index')
            ->with('success', __('clients.group_deleted'));
    }

    // ── Per-product pricing overrides ────────────────────────────────────────

    public function storePrice(Request $request, ClientGroup $clientGroup)
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'currency_code' => 'required|string|size:3',
            'billing_cycle' => 'required|string|max:30',
            'price'         => 'required|numeric|min:0',
            'setup_fee'     => 'nullable|numeric|min:0',
        ]);

        ClientGroupPricing::updateOrCreate(
            [
                'client_group_id' => $clientGroup->id,
                'product_id'      => $data['product_id'],
                'currency_code'   => strtoupper($data['currency_code']),
                'billing_cycle'   => $data['billing_cycle'],
            ],
            [
                'price'     => (int) round($data['price'] * 100),
                'setup_fee' => (int) round(($data['setup_fee'] ?? 0) * 100),
            ]
        );

        return redirect()->route('admin.client-groups.edit', $clientGroup)
            ->with('success', __('clients.price_override_saved'));
    }

    public function destroyPrice(Request $request, ClientGroup $clientGroup, ClientGroupPricing $price)
    {
        abort_if($price->client_group_id !== $clientGroup->id, 404);
        $price->delete();

        return back()->with('success', __('clients.price_override_removed'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);
    }

    /** Convert user-entered percentage (0-100) into basis points (0-10000). */
    private function normaliseDiscount(Request $request): int
    {
        $pct = (float) $request->input('discount_percent', 0);
        $pct = max(0, min(100, $pct));
        return (int) round($pct * 100);
    }
}
