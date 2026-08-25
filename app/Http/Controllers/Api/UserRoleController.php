<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(User $user)
    {
        return response()->json([
            'roles' => $user->roles,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['required', 'exists:roles,id'],
        ]);

        $roles = Role::whereIn('id', $request->roles)->get();

        $user->syncRoles($roles);

        return response()->json([
            'message' => 'Roles assigned successfully.',
            'user' => $user->load('roles'),
        ]);
    }

    public function destroy(User $user, Role $role)
    {
        $user->removeRole($role);

        return response()->json([
            'message' => 'Role removed successfully.',
        ]);
    }
}
