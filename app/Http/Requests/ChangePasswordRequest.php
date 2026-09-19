<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $isGoogleUserWithoutPassword = $user?->password === null && $user?->google_id !== null;
        $requiresCurrentPassword = ! $isGoogleUserWithoutPassword;

        return [
            'current_password' => $requiresCurrentPassword
                ? ['required', 'string']
                : ['nullable', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }
}
