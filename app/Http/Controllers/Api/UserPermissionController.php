<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    /**
     * Get user's direct permissions
     */
    public function index(User $user)
    {
        return response()->json([
            'user' => $user,
            'permissions' => $user->getDirectPermissions(),
        ]);
    }

    /**
     * Assign permissions directly to user
     */
    public function store(Request $request, User $user)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        foreach ($request->permissions as $permissionId) {
            $permission = Permission::findOrFail($permissionId);

            $user->givePermissionTo($permission);
        }

        return response()->json([
            'message' => 'Permissions assigned successfully.',
            'permissions' => $user->getDirectPermissions(),
        ]);
    }

    /**
     * Remove direct permission from user
     */
    public function destroy(User $user, Permission $permission)
    {
        $user->revokePermissionTo($permission);

        return response()->json([
            'message' => 'Permission removed successfully.',
            'user' => $user,
            'permissions' => $user->getDirectPermissions(),
        ]);
    }
}
