<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSpecificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product_id' => $this->product_id,

            'specification_id' => $this->specification_id,

            'value_ar' => $this->value_ar,

            'value_en' => $this->value_en,

            'sort_order' => $this->sort_order,

            'specification' => $this->whenLoaded(
                'specification',
                function ($specification) {
                    if (!$specification) {
                        return null;
                    }

                    return [
                        'id' => $specification->id,
                        'spec_key' => $specification->spec_key,
                        'name_ar' => $specification->name_ar,
                        'name_en' => $specification->name_en,
                        'unit' => $specification->unit,
                        'sort_order' => $specification->sort_order,
                        'status' => $specification->status,
                    ];
                }
            ),
        ];
    }
}
