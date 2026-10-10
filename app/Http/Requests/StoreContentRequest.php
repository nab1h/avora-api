<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $valueRules = match ($this->input('type')) {
            'number' => ['numeric'],
            'boolean' => ['boolean'],
            'text', 'textarea', 'url' => ['string'],
            default => [],
        };

        return [
            'page' => [
                'required',
                'string',
                'max:100',
            ],

            'section' => [
                'required',
                'string',
                'max:100',
            ],

            'key' => [
                'required',
                'string',
                'max:100',
                Rule::unique('contents')->where(function ($query) {
                    return $query
                        ->where('page', $this->page)
                        ->where('section', $this->section);
                }),
            ],

            'value' => [
                'nullable',
                ...$valueRules,
            ],

            'type' => [
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
                'nullable',
                'integer',
                'min:0',
            ],

            'gallery_id' => [
                'nullable',
                'exists:gallery_items,id',
                'required_if:type,image',
            ],
        ];
    }
}

