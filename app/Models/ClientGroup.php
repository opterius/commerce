<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientGroup extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
        'discount_percent',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ClientGroupPricing::class);
    }

    /**
     * Return the override price row for this group/product/currency/cycle, or null.
     */
    public function priceOverride(int $productId, string $currencyCode, string $cycle): ?ClientGroupPricing
    {
        return $this->pricing()
            ->where('product_id', $productId)
            ->where('currency_code', $currencyCode)
            ->where('billing_cycle', $cycle)
            ->first();
    }
}
