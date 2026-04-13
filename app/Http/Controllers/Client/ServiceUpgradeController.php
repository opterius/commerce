<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\{Product, ProductPricing, Service, ServiceUpgradeRequest, Setting};
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class ServiceUpgradeController extends Controller
{
    public function show(Service $service)
    {
        abort_if($service->client_id !== auth('client')->id(), 403);

        $service->load(['product.group', 'product.pricing']);

        $client = auth('client')->user();
        $currencyCode = $service->currency_code;

        // Load all products that have pricing in the same currency
        $products = Product::with(['pricing', 'group'])
            ->where('status', 'active')
            ->get();

        $invoiceService = app(InvoiceService::class);

        $pricingOptions = [];

        foreach ($products as $product) {
            foreach ($product->pricing as $pricing) {
                if ($pricing->currency_code !== $currencyCode) {
                    continue;
                }

                // Skip the exact same combination
                if (
                    $product->id === $service->product_id
                    && $pricing->billing_cycle === $service->billing_cycle
                ) {
                    continue;
                }

                $proration = $invoiceService->calculateProration($service, $pricing->price);

                $pricingOptions[] = [
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'group_name'    => $product->group?->name ?? '',
                    'billing_cycle' => $pricing->billing_cycle,
                    'price'         => $pricing->price,
                    'proration'     => $proration,
                    'is_upgrade'    => $pricing->price > $service->amount,
                ];
            }
        }

        // Sort: upgrades first, then by price ascending
        usort($pricingOptions, function ($a, $b) {
            if ($a['is_upgrade'] !== $b['is_upgrade']) {
                return $b['is_upgrade'] <=> $a['is_upgrade'];
            }
            return $a['price'] <=> $b['price'];
        });

        return view('client.services.upgrade', compact('service', 'pricingOptions', 'currencyCode'));
    }

    public function store(Request $request, Service $service)
    {
        abort_if($service->client_id !== auth('client')->id(), 403);

        $validated = $request->validate([
            'to_product_id'    => ['required', 'integer', 'exists:products,id'],
            'to_billing_cycle' => ['required', 'string', 'in:monthly,quarterly,semi_annual,annual,biennial,triennial'],
        ]);

        $service->load('product');
        $client = auth('client')->user();

        $pricing = ProductPricing::where('product_id', $validated['to_product_id'])
            ->where('billing_cycle', $validated['to_billing_cycle'])
            ->where('currency_code', $service->currency_code)
            ->first();

        if (! $pricing) {
            return back()->withErrors(['to_billing_cycle' => 'No pricing found for that product and billing cycle.']);
        }

        $invoiceService = app(InvoiceService::class);
        $proration = $invoiceService->calculateProration($service, $pricing->price);

        $upgradeRequest = ServiceUpgradeRequest::create([
            'service_id'         => $service->id,
            'client_id'          => $client->id,
            'from_product_id'    => $service->product_id,
            'from_billing_cycle' => $service->billing_cycle,
            'from_amount'        => $service->amount,
            'to_product_id'      => $validated['to_product_id'],
            'to_billing_cycle'   => $validated['to_billing_cycle'],
            'to_amount'          => $pricing->price,
            'proration_charge'   => $proration['net'],
            'status'             => 'pending',
        ]);

        $requireApproval = Setting::get('require_upgrade_approval', '0');

        if ($requireApproval === '0') {
            $invoiceService->applyUpgrade($upgradeRequest);

            return redirect()->route('client.services.show', $service)
                ->with('success', 'Your plan has been updated successfully.' . ($proration['net'] > 0 ? ' An invoice has been created for the proration charge.' : ($proration['net'] < 0 ? ' A credit has been applied to your account.' : '')));
        }

        return redirect()->route('client.services.show', $service)
            ->with('success', 'Your upgrade request has been submitted and is pending approval.');
    }
}
