<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    const GATEWAYS = [
        'stripe' => 'Stripe',
        'manual' => 'Manual',
    ];

    protected $fillable = [
        'invoice_id',
        'gateway',
        'transaction_id',
        'amount',
        'currency_code',
        'status',
        'method',
        'gateway_response',
        'refunded_at',
        'refund_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'gateway_response' => 'array',
            'refunded_at'      => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
