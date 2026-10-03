<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SeoPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page' => $this->page,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'og_title' => $this->og_title,
            'og_description' => $this->og_description,

            'og_image' => $this->og_image
                ? Storage::disk('public')->url($this->og_image)
                : null,

            'robots' => $this->robots,
            'canonical_url' => $this->canonical_url,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}