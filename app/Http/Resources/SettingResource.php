<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $value = $this->value;

        if ($this->type === 'image' && $value) {
            $value = Storage::disk('public')->url($value);
        }

        return [
            'key' => $this->key,
            'value' => $value,
            'type' => $this->type,
            'group' => $this->group,
        ];
    }
}