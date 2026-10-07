<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $galleryItem = $this->galleryItem;
        $imageUrl = $galleryItem?->image
            ? Storage::disk('public')->url($galleryItem->image)
            : null;

        return [
            'id' => $this->id,
            'page' => $this->page,
            'section' => $this->section,
            'key' => $this->key,
            'value' => $this->type === 'image' ? $imageUrl : $this->value,
            'type' => $this->type,
            'gallery_id' => $this->gallery_id,
            'gallery' => $galleryItem
                ? [
                    'id' => $galleryItem->id,
                    'image' => $imageUrl,
                ]
                : null,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}