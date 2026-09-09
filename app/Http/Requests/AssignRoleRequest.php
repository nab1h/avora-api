<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'roles' => [
                'array'
            ],


            'roles.*' => [
                'integer',
                'exists:roles,id'
            ],

        ];
    }
}
