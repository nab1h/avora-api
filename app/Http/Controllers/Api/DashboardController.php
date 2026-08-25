<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'users_count' => User::count(),

            'active_users' => User::where('is_active', true)->count(),

            'inactive_users' => User::where('is_active', false)->count(),

            'roles_count' => Role::count(),

            'permissions_count' => Permission::count(),
        ]);
    }
}