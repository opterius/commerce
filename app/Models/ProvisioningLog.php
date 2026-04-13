<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvisioningLog extends Model
{
    protected $table = 'provisioning_log';

    protected $fillable = [
        'service_id',
        'action',
        'status',
        'request',
        'response',
        'error',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'request'  => 'array',
            'response' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'triggered_by');
    }
}
