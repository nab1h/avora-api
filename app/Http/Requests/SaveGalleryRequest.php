<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->can('manage-settings');
    }

    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'item_ids' => ['sometimes', 'array', 'max:500'],
            'item_ids.*' => ['required', 'integer', 'distinct', 'exists:gallery_items,id'],
        ];
    }
}
