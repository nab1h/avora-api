<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'slug', 'description_ar', 'description_en',
        'gallery_id', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'status' => 'string'];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
