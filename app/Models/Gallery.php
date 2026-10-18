<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    protected $fillable = ['name'];

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
