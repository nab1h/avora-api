<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // ===========================
    // index
    // ===========================
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return RoleResource::collection($roles);
    }
    // ===========================
    // store
    // ===========================

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'sanctum',
        ]);

        return new RoleResource($role);
    }

    // ===========================
    // show
    // ===========================
    public function show(Role $role)
    {
        $role->load('permissions');

        return new RoleResource($role);
    }

    // ===========================
    // update
    // ===========================
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();

        $role->update([
            'name' => $validated['name'] ?? $role->name,
            'guard_name' => $validated['guard_name'] ?? $role->guard_name,
        ]);

        return new RoleResource($role->fresh());
    }

    // ===========================
    // destroy
    // ===========================
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
