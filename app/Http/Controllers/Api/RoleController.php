<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role,
        ], 201);
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        return response()->json([
            'role' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name,'.$role->id,
            ],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role' => $role,
        ]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'message' => 'Role not found.',
            ], 404);
        }

        if (blank($role->guard_name)) {
            $role->guard_name = config('auth.defaults.guard', 'web');
            $role->saveQuietly();
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}
