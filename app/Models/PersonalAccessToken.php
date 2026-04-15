<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PersonalAccessToken extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'token',
        'last_used_at',
        'expires_at',
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generate a new token, returning [model, plaintext].
     */
    public static function generate(Client $client, string $name): array
    {
        $plaintext = Str::random(40);
        $token = static::create([
            'client_id' => $client->id,
            'name'      => $name,
            'token'     => hash('sha256', $plaintext),
        ]);

        return [$token, $plaintext];
    }

    public static function findByPlaintext(string $plaintext): ?static
    {
        return static::where('token', hash('sha256', $plaintext))->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
