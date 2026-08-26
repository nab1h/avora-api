<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;


class PermissionController extends Controller
{
    // ===========================
    // index
    // ===========================
    public function index()
    {
        $permissions = Permission::all();

        return PermissionResource::collection($permissions);
    }

    // ===========================
    // store
    // ===========================
    public function store(StorePermissionRequest $request)
    {
        $validated = $request->validated();

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'sanctum',
        ]);

        return new PermissionResource($permission);
    }
    // ===========================
    // show
    // ===========================

    public function show(Permission $permission)
    {
        return new PermissionResource($permission);
    }
    // ===========================
    // update
    // ===========================

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $validated = $request->validated();

        $permission->update([
            'name' => $validated['name'] ?? $permission->name,
            'guard_name' => $validated['guard_name'] ?? $permission->guard_name,
        ]);

        return new PermissionResource($permission->fresh());
    }

    // ===========================
    // destroy
    // ===========================
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully.',
        ]);
    }
}
