<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
=======
>>>>>>> 678db43 (done content)

class Content extends Model
{
    protected $fillable = [
        'page',
        'section',
        'key',
        'value',
        'type',
<<<<<<< HEAD
        'gallery_id',
        'sort_order',
    ];

    public function galleryItem(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class, 'gallery_id');
    }
}
=======
        'sort_order',
    ];
}
>>>>>>> 678db43 (done content)
