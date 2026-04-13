<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $fillable = [
        'entity_type',
        'name',
        'field_type',
        'description',
        'options',
        'required',
        'show_on_order',
        'show_on_invoice',
        'admin_only',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'required' => 'boolean',
            'show_on_order' => 'boolean',
            'show_on_invoice' => 'boolean',
            'admin_only' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
