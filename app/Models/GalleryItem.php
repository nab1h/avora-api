<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryItem extends Model
{
    protected $fillable = [
        'image',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'gallery_id');
    }
}
