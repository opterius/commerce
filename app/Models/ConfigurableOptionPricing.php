<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurableOptionPricing extends Model
{
    protected $table = 'configurable_option_pricing';

    protected $fillable = [
        'option_value_id',
        'currency_code',
        'billing_cycle',
        'price',
    ];

    public function value(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOptionValue::class, 'option_value_id');
    }
}
