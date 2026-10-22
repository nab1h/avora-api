<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active
            && $this->user()->can('manage-settings');
    }

    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        $product = $this->route('product');

        $productId = $product instanceof Product
            ? $product->getKey()
            : $product;

        return [
            'category_id' => [
                $required,
                'integer',
                'exists:categories,id',
            ],

            'name_ar' => [$required, 'string', 'max:255'],
            'name_en' => [$required, 'string', 'max:255'],

            'slug' => [
                $required,
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($productId, 'id'),
            ],

            'sku' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId, 'id'),
            ],

            'description_ar' => [
                'sometimes',
                'nullable',
                'string',
                'max:10000',
            ],

            'description_en' => [
                'sometimes',
                'nullable',
                'string',
                'max:10000',
            ],

            'price' => [
                $required,
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],

            'sale_price' => [
                'sometimes',
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],

            'status' => [
                'sometimes',
                Rule::in(['active', 'inactive']),
            ],

            'is_featured' => ['sometimes', 'boolean'],

            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            // Product specifications
            'specifications' => [
                'sometimes',
                'array',
                'max:100',
            ],

            'specifications.*.specification_id' => [
                'required',
                'integer',
                'distinct',
                'exists:specifications,id',
            ],

            'specifications.*.value_ar' => [
                'sometimes',
                'nullable',
                'string',
                'max:10000',
            ],

            'specifications.*.value_en' => [
                'sometimes',
                'nullable',
                'string',
                'max:10000',
            ],

            'specifications.*.sort_order' => [
                'sometimes',
                'integer',
                'min:0',
                'max:4294967295',
            ],

            'color_ids' => [
                'sometimes',
                'array',
                'max:100',
            ],

            'color_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:colors,id',
            ],

        ];
    }
}
