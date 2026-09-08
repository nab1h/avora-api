<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ==========================================================================
    // register method to create a new user and return an access token
    // ===========================================================================
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->load(['roles', 'permissions']);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    // ==========================================================================
    // login method to revoke the user's current access token
    // ===========================================================================

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }
        $user->load('roles');
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    // ==========================================================================
    // show me
    // ==========================================================================
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    // ==========================================================================
    // Logout method to revoke the user's current access token
    // ===========================================================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // ==========================================================================
    // forgotPassword method to revoke the user's current access token
    // ===========================================================================

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->validated()
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status),
            ], 400);
        }

        return response()->json([
            'message' => 'Password reset link sent successfully.',
        ]);
    }

    // ==========================================================================
    // resetPassword method to revoke the user's current access token
    // ===========================================================================
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 400);
        }

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    // =========================================================================
    // changePassword method to revoke the user's current access token
    // =========================================================================

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // اختياري: تسجيل خروج من كل الأجهزة
        // $user->tokens()->delete();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    // =========================================================================
    // updateProfile method to revoke the user's current access token
    // =========================================================================
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$user->id,
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    // =========================================================================
    // googleRedirect method to redirect the user to Google's OAuth page
    // =========================================================================

    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleCallback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        // Find user by Google ID
        $user = User::where('google_id', $googleUser->getId())->first();

        // If not found, find by email
        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        // Create new user if not found
        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        // Link Google account to existing user
        if (! $user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }

        $token = $user->createToken('google-auth')->plainTextToken;

        return redirect(
            config('app.frontend_url')
    . '/auth/google/callback?token=' . $token
    . '&user=' . urlencode(json_encode(new UserResource($user)))
        );
    }

    // =========================================================================
    // facebookRedirect method to redirect the user to Facebook's OAuth page
    // =========================================================================
    public function facebookRedirect()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    // --------------
    // callback
    // --------------
    public function facebookCallback()
    {
        $facebookUser = Socialite::driver('facebook')
            ->stateless()
            ->user();

        $user = User::where('facebook_id', $facebookUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $facebookUser->getEmail())->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'facebook_id' => $facebookUser->getId(),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        if (! $user->facebook_id) {
            $user->update([
                'facebook_id' => $facebookUser->getId(),
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account is inactive.',
            ], 403);
        }

        $token = $user->createToken('facebook-auth')->plainTextToken;

        return response()->json([
            'message' => 'Facebook login successful.',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }
}
