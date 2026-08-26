<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRolePermissionsRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(Role $role)
    {
        return response()->json([
            'permissions' => $role->permissions
        ]);
    }

    public function store(AssignRolePermissionsRequest $request, Role $role)
    {
        $permissions = Permission::whereIn(
            'id',
            $request->validated()['permissions']
        )->get();

        $role->syncPermissions($permissions);

        return response()->json([
            'message' => 'Permissions assigned successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    public function destroy(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return response()->json([
            'message' => 'Permission removed successfully.',
        ]);
    }
}