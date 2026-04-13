<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country_code',
        'tax_id',
        'currency_code',
        'language',
        'client_group_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ClientGroup::class, 'client_group_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ClientTag::class, 'client_tag', 'client_id', 'tag_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'entity_id')
            ->where('entity_type', 'client');
    }
}
