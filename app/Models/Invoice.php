<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    const STATUSES = [
        'draft'     => 'Draft',
        'unpaid'    => 'Unpaid',
        'paid'      => 'Paid',
        'overdue'   => 'Overdue',
        'cancelled' => 'Cancelled',
        'refunded'  => 'Refunded',
    ];

    const STATUS_COLORS = [
        'draft'     => 'gray',
        'unpaid'    => 'yellow',
        'paid'      => 'green',
        'overdue'   => 'red',
        'cancelled' => 'gray',
        'refunded'  => 'purple',
    ];

    protected $fillable = [
        'client_id',
        'invoice_number',
        'status',
        'due_date',
        'paid_date',
        'subtotal',
        'tax',
        'total',
        'credit_applied',
        'currency_code',
        'notes',
        'sent_at',
        'overdue_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'            => 'date',
            'paid_date'           => 'datetime',
            'sent_at'             => 'datetime',
            'overdue_notified_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid' && $this->due_date && $this->due_date->isPast();
    }

    public function getAmountDueAttribute(): int
    {
        $paid = $this->payments()->where('status', 'completed')->sum('amount');
        return max(0, $this->total - $paid - $this->credit_applied);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }
}
