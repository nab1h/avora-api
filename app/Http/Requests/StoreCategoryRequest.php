<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->can('manage-settings');
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'name_ar' => [$required, 'string', 'max:255'],
            'name_en' => [$required, 'string', 'max:255'],
            'slug' => [$required, 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'description_ar' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'description_en' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'gallery_id' => ['sometimes', 'nullable', 'integer', 'exists:galleries,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:4294967295'],
        ];
    }
}
