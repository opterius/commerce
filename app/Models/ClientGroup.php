<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientGroup extends Model
{
    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
