<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'prefix',
        'suffix',
        'decimal_places',
        'exchange_rate',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function active()
    {
        return static::where('is_active', true)->get();
    }

    public function format(int $amount): string
    {
        $value = number_format(
            $amount / pow(10, $this->decimal_places),
            $this->decimal_places
        );

        if ($this->prefix) {
            return $this->prefix . $value;
        }

        if ($this->suffix) {
            return $value . ' ' . $this->suffix;
        }

        return $this->symbol . $value;
    }
}
