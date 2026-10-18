<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryItem extends Model
{
    protected $fillable = [
        'image',
        'gallery_id',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'gallery_id');
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }
}
