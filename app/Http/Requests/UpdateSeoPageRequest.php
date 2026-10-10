<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => [
                'required',
                'string',
                'max:100',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'keywords' => [
                'nullable',
                'string',
            ],

            'og_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'og_description' => [
                'nullable',
                'string',
            ],

            'og_image' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'robots' => [
                'nullable',
                'string',
                'max:100',
            ],

            'canonical_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ];
    }
}
