<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurableOption extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'option_type',
        'sort_order',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ConfigurableOptionGroup::class, 'group_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ConfigurableOptionValue::class, 'option_id')->orderBy('sort_order');
    }
}
