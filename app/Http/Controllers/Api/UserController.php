<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $perPage = $request->integer('per_page', 10);

        // sort by name or email
        $sort = $request->input('sort', 'created_at');

        $direction = 'asc';

        if (str_starts_with($sort, '-')) {
            $sort = ltrim($sort, '-');
            $direction = 'desc';
        }

        $allowedSorts = [
            'name',
            'email',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
            $direction = 'desc';
        }

        $users = User::with(['roles', 'permissions'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })

            // Filter by Role
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->role($request->role);
            })

            // Filter by Permission
            ->when($request->filled('permission'), function ($query) use ($request) {
                $query->permission($request->permission);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        return response()->json([
            'users' => $users,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update([
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'message' => $user->is_active
                ? 'User activated successfully.'
                : 'User deactivated successfully.',

            'user' => $user->fresh(),
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
