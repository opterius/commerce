<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainTld extends Model
{
    protected $fillable = [
        'tld',
        'is_active',
        'sort_order',
        'register_price',
        'renew_price',
        'transfer_price',
        'min_years',
        'max_years',
        'epp_required',
        'whois_privacy_available',
        'grace_period_days',
        'redemption_period_days',
        'currency_code',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'epp_required'            => 'boolean',
        'whois_privacy_available' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function registerPriceFormatted(): string
    {
        return number_format($this->register_price / 100, 2);
    }

    public function renewPriceFormatted(): string
    {
        return number_format($this->renew_price / 100, 2);
    }

    public function transferPriceFormatted(): string
    {
        return number_format($this->transfer_price / 100, 2);
    }
}
