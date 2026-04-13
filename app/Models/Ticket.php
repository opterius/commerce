<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    const STATUSES = [
        'open'           => 'Open',
        'answered'       => 'Answered',
        'customer_reply' => 'Customer Reply',
        'on_hold'        => 'On Hold',
        'closed'         => 'Closed',
    ];

    const PRIORITIES = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
        'urgent' => 'Urgent',
    ];

    protected $fillable = [
        'client_id',
        'department_id',
        'assigned_staff_id',
        'subject',
        'status',
        'priority',
        'last_reply_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
            'closed_at'     => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)
            ->where('is_internal_note', false)
            ->orderBy('created_at');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag', 'ticket_id', 'ticket_tag_id');
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'open'           => 'blue',
            'answered'       => 'green',
            'customer_reply' => 'amber',
            'on_hold'        => 'yellow',
            'closed'         => 'gray',
            default          => 'gray',
        };
    }

    public function priorityBadgeColor(): string
    {
        return match ($this->priority) {
            'low'    => 'gray',
            'medium' => 'blue',
            'high'   => 'orange',
            'urgent' => 'red',
            default  => 'gray',
        };
    }
}
