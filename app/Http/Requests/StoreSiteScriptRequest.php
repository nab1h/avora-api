<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'type' => [
                'required',
                Rule::in([
                    'analytics',
                    'pixel',
                    'verification',
                    'custom',
                ]),
            ],

            'url' => [
                'nullable',
                'url',
                'required_without:code',
            ],

            'code' => [
                'nullable',
                'string',
                'required_without:url',
            ],

            'placement' => [
                'required',
                Rule::in([
                    'head',
                    'body_start',
                    'body_end',
                ]),
            ],

            'strategy' => [
                'required',
                Rule::in([
                    'beforeInteractive',
                    'afterInteractive',
                    'lazyOnload',
                ]),
            ],

            'is_active' => ['sometimes', 'boolean'],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }
}