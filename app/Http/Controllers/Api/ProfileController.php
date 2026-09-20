<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $user->load(['roles', 'permissions']);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],

            'birthday' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'national_id' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'national_id')->ignore($user->id),
            ],

            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,PNG',
                'max:2048',
            ],
            'job' => ['sometimes', 'string', 'max:255'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reset email verification when email changes
        |--------------------------------------------------------------------------
        */
        if (
            isset($validated['email']) &&
            $validated['email'] !== $user->email
        ) {
            $validated['email_verified_at'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Avatar
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('avatar')) {

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')
                ->store('users', 'public');
        }
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ], 200);
    }
}
