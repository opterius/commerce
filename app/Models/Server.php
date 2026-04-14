<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    protected $fillable = [
        'server_group_id',
        'type',
        'name',
        'hostname',
        'ip_address',
        'credentials',
        'max_accounts',
        'account_count',
        'ns1',
        'ns2',
        'ns3',
        'ns4',
        'is_active',
    ];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return [
            'credentials'   => 'array',
            'is_active'     => 'boolean',
            'max_accounts'  => 'integer',
            'account_count' => 'integer',
        ];
    }

    public function serverGroup(): BelongsTo
    {
        return $this->belongsTo(ServerGroup::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function provisioningLogs(): HasMany
    {
        return $this->hasMany(ProvisioningLog::class);
    }

    public function isFull(): bool
    {
        return $this->max_accounts > 0 && $this->account_count >= $this->max_accounts;
    }
}
