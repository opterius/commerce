<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUpgradeRequest extends Model
{
    protected $fillable = [
        'service_id',
        'client_id',
        'from_product_id',
        'from_billing_cycle',
        'from_amount',
        'to_product_id',
        'to_billing_cycle',
        'to_amount',
        'proration_charge',
        'invoice_id',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'from_amount'  => 'integer',
            'to_amount'    => 'integer',
            'proration_charge' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fromProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'from_product_id');
    }

    public function toProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'to_product_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'processed_by');
    }

    public function isUpgrade(): bool
    {
        return $this->to_amount > $this->from_amount;
    }
}
