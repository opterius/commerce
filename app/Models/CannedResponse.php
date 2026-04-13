<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    protected $fillable = [
        'department_id',
        'staff_id',
        'title',
        'body',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Replace template variables with real values.
     */
    public function render(Ticket $ticket): string
    {
        $client = $ticket->client;

        return str_replace(
            ['{client_name}', '{client_email}', '{ticket_id}', '{department}'],
            [
                trim($client->first_name . ' ' . $client->last_name),
                $client->email,
                $ticket->id,
                $ticket->department->name ?? '',
            ],
            $this->body
        );
    }
}
