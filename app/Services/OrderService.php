<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ConfigurableOptionValue;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Create an order for a client, resolving all pricing in the client's currency.
     *
     * @param  array<int>        $selectedValueIds  ConfigurableOptionValue IDs selected by the client
     * @throws \RuntimeException if the product has no pricing for any available currency
     */
    public function create(
        Client  $client,
        Product $product,
        string  $cycle,
        array   $selectedValueIds = [],
        ?string $promoCode        = null,
    ): Order {
        $product->load(['pricing', 'configurableOptionGroups.options.values.pricing']);
        $client->loadMissing('group.pricing');

        $currencyCode = $this->resolveClientCurrency($client);

        // ── Resolve base product price ────────────────────────────────────────
        $pricing = $product->getPriceForCycle($currencyCode, $cycle);

        if (! $pricing) {
            // Fall back to the system default currency if product isn't priced in client's currency
            $default = Currency::getDefault();
            if ($default && $default->code !== $currencyCode) {
                $pricing      = $product->getPriceForCycle($default->code, $cycle);
                $currencyCode = $default->code;
            }
        }

        if (! $pricing) {
            throw new \RuntimeException("Product is not available for the billing cycle '{$cycle}'.");
        }

        // ── Client group pricing override (bypasses group discount if set) ──
        $basePrice    = (int) $pricing->price;
        $baseSetupFee = (int) $pricing->setup_fee;
        $usedOverride = false;

        if ($client->group) {
            $override = $client->group->priceOverride($product->id, $currencyCode, $cycle);
            if ($override) {
                $basePrice    = (int) $override->price;
                $baseSetupFee = (int) $override->setup_fee;
                $usedOverride = true;
            }
        }

        // ── Resolve configurable option prices ────────────────────────────────
        $optionTotal   = 0;
        $configOptions = [];

        foreach ($selectedValueIds as $valueId) {
            $value = ConfigurableOptionValue::with(['pricing', 'option'])->find((int) $valueId);

            if (! $value) continue;

            $optionPrice = $this->resolveOptionPrice($value, $currencyCode, $cycle);
            $optionTotal += $optionPrice;

            $configOptions[$value->option->name] = [
                'label' => $value->label,
                'price' => $optionPrice,
            ];
        }

        // ── Group discount (applied only when no per-product override) ───────
        $groupDiscount = 0;
        if (! $usedOverride && $client->group && $client->group->discount_percent > 0) {
            $discountable  = $basePrice + $optionTotal;
            $groupDiscount = (int) round($discountable * ($client->group->discount_percent / 10000));
        }

        // ── Promo code ────────────────────────────────────────────────────────
        $discount   = $groupDiscount;
        $promoModel = null;

        if ($promoCode) {
            $promoModel = PromoCode::where('code', strtoupper(trim($promoCode)))->first();

            if ($promoModel && $promoModel->isValid() && $this->promoAppliesToProduct($promoModel, $product)) {
                $base     = max(0, ($basePrice + $optionTotal) - $groupDiscount);
                $discount = $groupDiscount + $this->calculateDiscount($base, $promoModel);
            } else {
                $promoModel = null; // invalid — don't attach
            }
        }

        // ── Totals ────────────────────────────────────────────────────────────
        $subtotal = $basePrice + $baseSetupFee + $optionTotal;
        $total    = max(0, $subtotal - $discount);

        // ── Persist ───────────────────────────────────────────────────────────
        return DB::transaction(function () use (
            $client, $product, $cycle, $currencyCode,
            $basePrice, $baseSetupFee, $configOptions, $promoModel,
            $subtotal, $discount, $total
        ) {
            $order = Order::create([
                'client_id'     => $client->id,
                'promo_code_id' => $promoModel?->id,
                'status'        => 'pending',
                'currency_code' => $currencyCode,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'total'         => $total,
                'ip_address'    => request()->ip(),
            ]);

            OrderItem::create([
                'order_id'       => $order->id,
                'product_id'     => $product->id,
                'billing_cycle'  => $cycle,
                'qty'            => 1,
                'price'          => $basePrice,
                'setup_fee'      => $baseSetupFee,
                'config_options' => $configOptions ?: null,
            ]);

            // Increment promo usage
            $promoModel?->increment('uses');

            // Generate the invoice immediately
            $invoice = $this->invoiceService->createForOrder($order);
            $order->update(['invoice_id' => $invoice->id]);

            return $order->fresh();
        });
    }

    /**
     * Validate a promo code for a product and return discount info, or null if invalid.
     *
     * @return array{code: string, discount: int, type: string}|null
     */
    public function validatePromoCode(string $code, Product $product, string $currencyCode, string $cycle, int $basePrice): ?array
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (! $promo || ! $promo->isValid() || ! $this->promoAppliesToProduct($promo, $product)) {
            return null;
        }

        $discount = $this->calculateDiscount($basePrice, $promo);

        return [
            'code'     => $promo->code,
            'type'     => $promo->type,
            'value'    => $promo->value,
            'discount' => $discount,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveClientCurrency(Client $client): string
    {
        if (! empty($client->currency_code)) {
            return $client->currency_code;
        }

        return Currency::getDefault()?->code ?? 'USD';
    }

    private function resolveOptionPrice(ConfigurableOptionValue $value, string $currencyCode, string $cycle): int
    {
        $price = $value->pricing
            ->where('currency_code', $currencyCode)
            ->where('billing_cycle', $cycle)
            ->first();

        if (! $price) {
            // Fall back to default currency
            $default = Currency::getDefault();
            if ($default) {
                $price = $value->pricing
                    ->where('currency_code', $default->code)
                    ->where('billing_cycle', $cycle)
                    ->first();
            }
        }

        return (int) ($price?->price ?? 0);
    }

    private function promoAppliesToProduct(PromoCode $promo, Product $product): bool
    {
        if ($promo->applies_to === 'all') {
            return true;
        }

        return $promo->products()->where('product_id', $product->id)->exists();
    }

    private function calculateDiscount(int $baseAmount, PromoCode $promo): int
    {
        if ($promo->type === 'percent') {
            return (int) round($baseAmount * ($promo->value / 10000)); // value stored as basis points (e.g. 1000 = 10%)
        }

        return min($baseAmount, (int) $promo->value);
    }
}
