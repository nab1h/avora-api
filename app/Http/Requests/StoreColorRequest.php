<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) (
            $this->user()?->is_active
            && $this->user()->can('manage-settings')
        );
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST')
            ? 'required'
            : 'sometimes';

        return [
            'name_ar' => [
                $required,
                'string',
                'max:100',
            ],

            'name_en' => [
                $required,
                'string',
                'max:100',
            ],

            'hex' => [
                $required,
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            'status' => [
                'sometimes',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }
}
