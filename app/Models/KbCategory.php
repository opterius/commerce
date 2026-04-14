<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KbCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'sort_order', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(KbArticle::class, 'category_id')->orderBy('sort_order');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
