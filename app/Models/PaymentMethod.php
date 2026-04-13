<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    protected $fillable = [
        'client_id',
        'stripe_pm_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'exp_month'  => 'integer',
            'exp_year'   => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getExpiryAttribute(): string
    {
        return str_pad($this->exp_month, 2, '0', STR_PAD_LEFT) . '/' . substr($this->exp_year, -2);
    }

    public function getIsExpiredAttribute(): bool
    {
        $now = now();
        return $this->exp_year < $now->year
            || ($this->exp_year === $now->year && $this->exp_month < $now->month);
    }
}
