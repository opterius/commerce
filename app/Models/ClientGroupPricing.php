<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientGroupPricing extends Model
{
    protected $table = 'client_group_pricing';

    protected $fillable = [
        'client_group_id', 'product_id', 'currency_code', 'billing_cycle',
        'price', 'setup_fee',
    ];

    protected $casts = [
        'price'     => 'integer',
        'setup_fee' => 'integer',
    ];

    public function clientGroup(): BelongsTo
    {
        return $this->belongsTo(ClientGroup::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
