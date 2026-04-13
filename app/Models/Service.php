<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Service extends Model
{
    const STATUSES = [
        'pending'    => 'Pending',
        'active'     => 'Active',
        'suspended'  => 'Suspended',
        'terminated' => 'Terminated',
        'cancelled'  => 'Cancelled',
    ];

    protected $fillable = [
        'client_id',
        'product_id',
        'order_id',
        'order_item_id',
        'server_id',
        'panel_account_id',
        'status',
        'domain',
        'username',
        'billing_cycle',
        'amount',
        'currency_code',
        'next_due_date',
        'last_due_date',
        'registration_date',
        'suspended_at',
        'terminated_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_due_date'     => 'date',
            'last_due_date'     => 'date',
            'registration_date' => 'date',
            'suspended_at'      => 'datetime',
            'terminated_at'     => 'datetime',
            'cancelled_at'      => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function provisioningLogs(): HasMany
    {
        return $this->hasMany(ProvisioningLog::class);
    }

    public function needsProvisioning(): bool
    {
        return $this->product && $this->product->provisioning_module;
    }
}
