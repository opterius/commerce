<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainContact extends Model
{
    protected $fillable = [
        'domain_id',
        'type',
        'registrar_contact_id',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country_code',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
