<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleDeleteWithNullGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_with_blank_guard_name_can_be_deleted(): void
    {
        DB::table('roles')->insert([
            'name' => 'editor',
            'guard_name' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = Role::where('name', 'editor')->firstOrFail();

        $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => '']);

        $role->delete();

        $this->assertDatabaseMissing('roles', ['name' => 'editor']);
    }
}
