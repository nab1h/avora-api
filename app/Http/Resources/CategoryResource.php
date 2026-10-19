<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'slug' => $this->slug,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'status' => $this->status,
            'sort_order' => $this->sort_order,

            'gallery_item_id' => $this->gallery_item_id,

            'gallery_item' => $this->whenLoaded(
                'galleryItem',
                fn () => $this->galleryItem
                    ? [
                        'id' => $this->galleryItem->id,
                        'image' => $this->galleryItem->image,
                    ]
                    : null
            ),

            'products' => new ProductCollection(
                $this->whenLoaded('products')
            ),
        ];
    }
}
