<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    const STATUSES = [
        'pending'   => 'Pending',
        'active'    => 'Active',
        'fraud'     => 'Fraud',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'client_id',
        'promo_code_id',
        'invoice_id',
        'status',
        'currency_code',
        'subtotal',
        'discount',
        'total',
        'notes',
        'ip_address',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
