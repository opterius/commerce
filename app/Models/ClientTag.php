<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClientTag extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_tag', 'tag_id', 'client_id');
    }
}
