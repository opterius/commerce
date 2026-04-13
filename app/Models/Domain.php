<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    public const STATUSES = [
        'pending'          => 'Pending',
        'active'           => 'Active',
        'expired'          => 'Expired',
        'transferred_away' => 'Transferred Away',
        'cancelled'        => 'Cancelled',
        'fraud'            => 'Fraud',
        'redemption'       => 'Redemption',
    ];

    public const BILLING_CYCLES = [
        '1year'  => '1 Year',
        '2year'  => '2 Years',
        '3year'  => '3 Years',
        '5year'  => '5 Years',
        '10year' => '10 Years',
    ];

    protected $fillable = [
        'client_id',
        'order_id',
        'order_item_id',
        'domain_name',
        'tld',
        'status',
        'registrar_module',
        'registrar_order_id',
        'registration_date',
        'expiry_date',
        'auto_renew',
        'whois_privacy',
        'is_locked',
        'epp_code',
        'ns1',
        'ns2',
        'ns3',
        'ns4',
        'billing_cycle',
        'amount',
        'currency_code',
        'next_due_date',
        'last_due_date',
        'notes',
    ];

    protected $casts = [
        'auto_renew'        => 'boolean',
        'whois_privacy'     => 'boolean',
        'is_locked'         => 'boolean',
        'registration_date' => 'date',
        'expiry_date'       => 'date',
        'next_due_date'     => 'date',
        'last_due_date'     => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DomainContact::class);
    }

    public function registrant(): ?DomainContact
    {
        return $this->contacts->firstWhere('type', 'registrant');
    }

    public function registrationYears(): int
    {
        return (int) str_replace('year', '', $this->billing_cycle);
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'active'           => 'green',
            'pending'          => 'yellow',
            'expired'          => 'red',
            'redemption'       => 'orange',
            'transferred_away' => 'gray',
            'cancelled'        => 'gray',
            'fraud'            => 'red',
            default            => 'gray',
        };
    }

    public function sld(): string
    {
        return str_replace('.' . $this->tld, '', $this->domain_name);
    }

    public function nameservers(): array
    {
        return array_filter([$this->ns1, $this->ns2, $this->ns3, $this->ns4]);
    }
}
