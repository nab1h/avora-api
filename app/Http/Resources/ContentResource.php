<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
<<<<<<< HEAD
        $galleryItem = $this->galleryItem;
        $imageUrl = $galleryItem?->image
            ? Storage::disk('public')->url($galleryItem->image)
            : null;
=======
        $value = $this->value;

        if ($this->type === 'image' && $value) {
            $value = Storage::disk('public')->url($value);
        }
>>>>>>> 678db43 (done content)

        return [
            'id' => $this->id,
            'page' => $this->page,
            'section' => $this->section,
            'key' => $this->key,
<<<<<<< HEAD
            'value' => $this->type === 'image' ? $imageUrl : $this->value,
            'type' => $this->type,
            'gallery_id' => $this->gallery_id,
            'gallery' => $galleryItem
                ? [
                    'id' => $galleryItem->id,
                    'image' => $imageUrl,
                ]
                : null,
=======
            'value' => $value,
            'type' => $this->type,
>>>>>>> 678db43 (done content)
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}