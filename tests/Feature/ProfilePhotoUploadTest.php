<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->post('/api/profile', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertOk();

        $avatarPath = $response->json('user.avatar');

        $this->assertNotNull($avatarPath);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'avatar' => $avatarPath,
        ]);
        Storage::disk('public')->assertExists($avatarPath);
    }
}
