<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricing extends Model
{
    protected $table = 'product_pricing';

    protected $fillable = [
        'product_id',
        'currency_code',
        'billing_cycle',
        'price',
        'setup_fee',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
