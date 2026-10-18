<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CategoryIndexRequest extends CatalogIndexRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'sort' => ['sometimes', Rule::in(['sort_order', 'name_en', 'created_at'])],
        ]);
    }
}
