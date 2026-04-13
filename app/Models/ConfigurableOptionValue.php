<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurableOptionValue extends Model
{
    protected $fillable = [
        'option_id',
        'label',
        'sort_order',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOption::class, 'option_id');
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ConfigurableOptionPricing::class, 'option_value_id');
    }
}
