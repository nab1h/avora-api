<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Content extends Model
{
    protected $fillable = [
        'page',
        'section',
        'key',
        'value',
        'type',
        'gallery_id',
        'sort_order',
    ];

    public function galleryItem(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class, 'gallery_id');
    }
}
