<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'product_group_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'provisioning_module',
        'stock_control',
        'qty_in_stock',
        'require_domain',
        'sort_order',
        'welcome_email_template',
    ];

    protected function casts(): array
    {
        return [
            'stock_control' => 'boolean',
            'require_domain' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ProductPricing::class);
    }

    public function configurableOptionGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ConfigurableOptionGroup::class,
            'product_configurable_group',
            'product_id',
            'configurable_option_group_id'
        );
    }

    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_product');
    }

    public function getPriceForCycle(string $currencyCode, string $cycle): ?ProductPricing
    {
        return $this->pricing
            ->where('currency_code', $currencyCode)
            ->where('billing_cycle', $cycle)
            ->first();
    }
}
