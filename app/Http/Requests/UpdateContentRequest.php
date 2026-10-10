<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type', $this->route('content')?->type);
        $valueRules = match ($type) {
            'number' => ['numeric'],
            'boolean' => ['boolean'],
            'text', 'textarea', 'url' => ['string'],
            default => [],
        };

        return [
            'value' => [
                'sometimes',
                'nullable',
                ...$valueRules,
            ],

            'type' => [
                'sometimes',
                'required',
                Rule::in([
                    'text',
                    'textarea',
                    'image',
                    'url',
                    'number',
                    'boolean',
                ]),
            ],

            'sort_order' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],

            'gallery_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'image'
                    || ($this->route('content')?->type === 'image' && $this->exists('gallery_id'))),
                'nullable',
                'integer',
                'exists:gallery_items,id',
            ],
        ];
    }
}