<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $value = $this->value;

        if ($this->type === 'image' && $value) {
            $value = Storage::disk('public')->url($value);
        }

        return [
            'id' => $this->id,
            'page' => $this->page,
            'section' => $this->section,
            'key' => $this->key,
            'value' => $value,
            'type' => $this->type,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}