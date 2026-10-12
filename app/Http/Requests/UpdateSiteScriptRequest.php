<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteScriptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'type' => [
                'sometimes',
                'required',
                Rule::in([
                    'analytics',
                    'pixel',
                    'verification',
                    'custom',
                ]),
            ],

            'url' => [
                'sometimes',
                'nullable',
                'url',
                'required_without:code',
            ],

            'code' => [
                'sometimes',
                'nullable',
                'string',
                'required_without:url',
            ],

            'placement' => [
                'sometimes',
                'required',
                Rule::in([
                    'head',
                    'body_start',
                    'body_end',
                ]),
            ],

            'strategy' => [
                'sometimes',
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