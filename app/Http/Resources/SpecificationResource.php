<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'spec_key' => $this->spec_key,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'unit' => $this->unit,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
        ];
    }
}