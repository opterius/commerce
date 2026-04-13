<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Pick the least-loaded active server in this group. */
    public function bestServer(): ?Server
    {
        return $this->servers()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('max_accounts', 0)
                  ->orWhereColumn('account_count', '<', 'max_accounts');
            })
            ->orderBy('account_count')
            ->first();
    }
}
