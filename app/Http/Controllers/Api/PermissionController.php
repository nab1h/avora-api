<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Permission created successfully.',
            'permission' => $permission,
        ], 201);
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name,' . $permission->id,
            ],
        ]);

        $permission->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Permission updated successfully.',
            'permission' => $permission,
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully.',
        ]);
    }
}