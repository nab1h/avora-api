<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specification extends Model
{
    protected $fillable = [
        'spec_key',
        'name_ar',
        'name_en',
        'unit',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'string',
        ];
    }

    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_specifications'
        )->withPivot([
            'id',
            'value_ar',
            'value_en',
            'sort_order',
        ])->withTimestamps();
    }
}
