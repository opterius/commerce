<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'recurring',
        'applies_to',
        'max_uses',
        'uses',
        'start_date',
        'end_date',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recurring' => 'boolean',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_code_product');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses && $this->uses >= $this->max_uses) return false;
        if ($this->start_date && now()->lt($this->start_date)) return false;
        if ($this->end_date && now()->gt($this->end_date)) return false;
        return true;
    }

    public function formattedValue(string $currencySymbol = '$'): string
    {
        if ($this->type === 'percent') {
            return number_format($this->value / 100, 2) . '%';
        }
        return $currencySymbol . number_format($this->value / 100, 2);
    }
}
