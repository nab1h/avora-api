<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $invitations = Invitation::with(['role', 'inviter'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'invitations' => $invitations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $invitation = Invitation::create([
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'invited_by' => $request->user()->id,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new InvitationNotification($invitation));

        return response()->json([
            'message' => 'Invitation created successfully.',
        ], 201);
    }

    public function accept(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $invitation = Invitation::where('token', $validated['token'])
            ->first();

        if (! $invitation) {
            return response()->json([
                'message' => 'Invalid invitation.',
            ], 404);
        }

        if ($invitation->isRevoked()) {
            return response()->json([
                'message' => 'This invitation has been revoked.',
            ], 410);
        }

        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'This invitation has already been accepted.',
            ], 409);
        }

        if ($invitation->isExpired()) {
            return response()->json([
                'message' => 'This invitation has expired.',
            ], 410);
        }

        if (User::where('email', $invitation->email)->exists()) {
            return response()->json([
                'message' => 'A user with this email already exists.',
            ], 409);
        }

        $user = DB::transaction(function () use ($validated, $invitation) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            $user->assignRole($invitation->role);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            return $user;
        });

        $token = $user->createToken('invitation-auth')->plainTextToken;

        return response()->json([
            'message' => 'Invitation accepted successfully.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function revoke(Invitation $invitation)
    {
        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'Accepted invitations cannot be revoked.',
            ], 409);
        }

        if ($invitation->isRevoked()) {
            return response()->json([
                'message' => 'Invitation is already revoked.',
            ], 409);
        }

        $invitation->update([
            'revoked_at' => now(),
        ]);

        return response()->json([
            'message' => 'Invitation revoked successfully.',
        ]);
    }

    public function resend(Invitation $invitation)
    {
        if ($invitation->isAccepted()) {
            return response()->json([
                'message' => 'Accepted invitations cannot be resent.',
            ], 409);
        }

        if ($invitation->isRevoked()) {
            return response()->json([
                'message' => 'Revoked invitations cannot be resent.',
            ], 409);
        }

        $invitation->update([
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new InvitationNotification($invitation));

        return response()->json([
            'message' => 'Invitation resent successfully.',
        ]);
    }
}
