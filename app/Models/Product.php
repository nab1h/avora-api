<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'slug',
        'sku',
        'description_ar',
        'description_en',
        'price',
        'sale_price',
        'status',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'status' => 'string',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    // Temporary compatibility with existing code.
    public function specifications(): HasMany
    {
        return $this->productSpecifications();
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->whereHas(
                'category',
                fn (Builder $category) => $category->visible()
            );
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(
            Color::class,
            'product_colors'
        )
            ->withPivot(['id', 'sort_order'])
            ->withTimestamps()
            ->orderBy('product_colors.sort_order');
    }
}
