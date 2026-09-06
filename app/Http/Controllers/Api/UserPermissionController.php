<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignPermissionRequest;
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
    public function store(AssignPermissionRequest $request, User $user)
    {
        $permissions = Permission::whereIn(
        'id',
        $request->validated()['permissions']
    )->get();

        $user->syncPermissions($permissions);

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
