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
<<<<<<< HEAD
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
=======
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

                Rule::unique('contents')
                    ->where(function ($query) {
                        return $query
                            ->where('page', $this->input('page'))
                            ->where('section', $this->input('section'));
                    })
                    ->ignore($this->route('content'), 'id'),
            ],

            'value' => [
                'nullable',
                'string',
            ],

            'type' => [
>>>>>>> 678db43 (done content)
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
<<<<<<< HEAD
                'sometimes',
=======
>>>>>>> 678db43 (done content)
                'nullable',
                'integer',
                'min:0',
            ],

<<<<<<< HEAD
            'gallery_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'image'
                    || ($this->route('content')?->type === 'image' && $this->exists('gallery_id'))),
                'nullable',
                'integer',
                'exists:gallery_items,id',
=======
            'image' => [
                'nullable',
                'image',
                'max:2048',
>>>>>>> 678db43 (done content)
            ],
        ];
    }
}
