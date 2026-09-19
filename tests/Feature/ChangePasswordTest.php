<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_user_can_create_a_password_without_current_password(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => 'google-user-id',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/change-password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Password changed successfully.',
            ])
            ->assertJsonMissingPath('password');

        $user->refresh();

        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_existing_password_user_must_provide_the_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'CurrentPassword123!',
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/change-password', [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $user->refresh();

        $this->assertTrue(Hash::check('CurrentPassword123!', $user->password));
    }
}