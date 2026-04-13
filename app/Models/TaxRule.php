<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $fillable = [
        'name',
        'country_code',
        'state_code',
        'rate',
        'applies_to',
        'is_eu_tax',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate'       => 'decimal:2',
            'is_eu_tax'  => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', strtoupper($countryCode));
    }
}
