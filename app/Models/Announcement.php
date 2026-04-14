<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'priority',
        'published_at', 'expires_at',
        'is_featured', 'show_public', 'show_client',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        'is_featured'  => 'boolean',
        'show_public'  => 'boolean',
        'show_client'  => 'boolean',
    ];

    public const PRIORITIES = ['info', 'success', 'warning', 'critical'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Scope: currently active (published, not expired). */
    public function scopeActive(Builder $q): Builder
    {
        $now = now();
        return $q->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now));
    }

    /** Scope: visible on the public portal. */
    public function scopePublic(Builder $q): Builder
    {
        return $q->where('show_public', true);
    }

    /** Scope: visible inside the client area. */
    public function scopeClient(Builder $q): Builder
    {
        return $q->where('show_client', true);
    }

    /** Scope: featured (shown as banner). */
    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }
}
