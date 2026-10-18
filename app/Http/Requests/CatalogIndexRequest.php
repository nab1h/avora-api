<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1', 'max:1000000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'category_id' => ['sometimes', 'integer', 'min:1'],
            'featured' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', Rule::in(['sort_order', 'price', 'name_en', 'created_at'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
