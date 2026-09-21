<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,

            'platform' => [
                'id' => $this->platform->id,
                'name' => $this->platform->name,
                'slug' => $this->platform->slug,
                'icon' => $this->platform->icon,
            ],

            'url' => $this->url,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
