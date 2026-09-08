<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Notifications\VerifyEmailNotification;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verify the signed URL
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'message' => 'Invalid or expired verification link.',
            ], 403);
        }

        // Verify the hash
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 403);
        }

        // Already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    // --------------------------------------
    // Resend verification email
    // --------------------------------------
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        // Laravel verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // Convert Laravel URL to Frontend URL
        $frontendUrl = config('app.frontend_url') . '/email-verify/'
            . $user->getKey() . '/' . sha1($user->getEmailForVerification())
            . '?' . parse_url($verificationUrl, PHP_URL_QUERY);

        // Send notification
        $user->notify(new VerifyEmailNotification($frontendUrl));

        return response()->json([
            'message' => 'Verification email sent successfully.',
            'url' => $frontendUrl,
        ]);
    }
}
