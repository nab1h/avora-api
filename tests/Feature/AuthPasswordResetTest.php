<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_link_is_sent_to_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ])
            ->assertOk()
            ->assertJson([
                'message' => 'Password reset link sent successfully.',
            ]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function ($notification, $channels) use ($user) {
                $this->assertNotNull($notification->token);
                $this->assertContains('mail', $channels);
                $this->assertStringContainsString('email=' . urlencode($user->email), $notification->toMail($user)->actionUrl);

                return true;
            }
        );
    }
}
