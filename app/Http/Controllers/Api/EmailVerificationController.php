<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent successfully.',
        ]);
    }
}
