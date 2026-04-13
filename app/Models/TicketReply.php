<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'staff_id',
        'client_id',
        'body',
        'is_internal_note',
    ];

    protected function casts(): array
    {
        return [
            'is_internal_note' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function authorName(): string
    {
        if ($this->staff) {
            return $this->staff->name;
        }

        if ($this->client) {
            return trim($this->client->first_name . ' ' . $this->client->last_name);
        }

        return 'Unknown';
    }

    public function isFromStaff(): bool
    {
        return $this->staff_id !== null;
    }
}
