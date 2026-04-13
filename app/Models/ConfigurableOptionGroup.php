<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigurableOptionGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(ConfigurableOption::class, 'group_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_configurable_group',
            'configurable_option_group_id',
            'product_id'
        );
    }
}
