<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbArticle extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'excerpt', 'content',
        'views', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'views'        => 'integer',
        'sort_order'   => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KbCategory::class, 'category_id');
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
