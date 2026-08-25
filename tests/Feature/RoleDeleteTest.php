<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_can_be_deleted_without_relationship_error(): void
    {
        $role = Role::create(['name' => 'admin']);

        $this->assertDatabaseHas('roles', ['name' => 'admin']);

        $role->delete();

        $this->assertDatabaseMissing('roles', ['name' => 'admin']);
    }
}
