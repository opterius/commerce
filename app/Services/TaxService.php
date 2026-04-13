<?php

namespace App\Services;

use App\Models\{Client, TaxRule};

class TaxService
{
    public function calculate(Client $client, int $amountCents, string $productType = 'all'): int
    {
        $rule = $this->findRule($client, $productType);

        if (!$rule) {
            return 0;
        }

        // EU B2B reverse charge: zero-rate if client has a VAT number
        if ($rule->is_eu_tax && !empty($client->tax_id)) {
            return 0;
        }

        return (int) floor($amountCents * $rule->rate / 100);
    }

    public function getRateForClient(Client $client, string $productType = 'all'): float
    {
        $rule = $this->findRule($client, $productType);
        return $rule ? (float) $rule->rate : 0.0;
    }

    private function findRule(Client $client, string $productType): ?TaxRule
    {
        return TaxRule::active()
            ->forCountry($client->country_code ?? '')
            ->whereIn('applies_to', ['all', $productType])
            ->orderBy('sort_order')
            ->first();
    }
}
