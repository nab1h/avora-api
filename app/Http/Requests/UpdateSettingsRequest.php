<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => [
                'required',
                'string',
                'max:255',
            ],

            'site_description' => [
                'nullable',
                'string',
            ],

            'site_logo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'site_favicon' => [
                'nullable',
                'image',
                'max:1024',
            ],

            'site_apple_icon' => [
                'nullable',
                'image',
                'dimensions:width=180,height=180',
                'max:2048',
            ],

            'site_android_icon' => [
                'nullable',
                'image',
                'dimensions:width=192,height=192',
                'max:2048',
            ],

            'site_maskable_icon' => [
                'nullable',
                'image',
                'dimensions:width=512,height=512',
                'max:2048',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'contact_address' => [
                'nullable',
                'string',
            ],

            'contact_address_ar' => [
                'nullable',
                'string',
            ],
            'contact_whatsapp' => [
                'nullable', 
                'string', 
                'max:50'
            ],

            'google_maps_url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'google_maps_embed_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
        ];
    }
}