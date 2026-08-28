<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==========================
// auth routes
// ==========================
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ==========================
// google auth routes
// ==========================
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
// ==========================
// edit profile routes
// ==========================
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});

// ==========================
// forgot password route
// ==========================
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

// ==========================
// email verification routes
// ==========================
Route::get('/email/verify/{id}/{hash}',[EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification',[EmailVerificationController::class, 'resend'])->middleware(['auth:sanctum','throttle:5,60',]);

// ==========================================================================================================
// ==========================================================================================================
// PROMSSIONS AND ROLES ROUTES
// ==========================================================================================================
// ==========================================================================================================

// ==========================
// manage-roles
// ==========================

Route::middleware(['auth:sanctum', 'permission:manage-roles', 'verified' , 'active'])->group(function () {

    Route::apiResource('roles', RoleController::class);

    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'store']);
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index']);
    Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy']);

    Route::post('users/{user}/roles', [UserRoleController::class, 'store']);
    Route::get('users/{user}/roles', [UserRoleController::class, 'index']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'destroy']);

    Route::post('users/{user}/permissions', [UserPermissionController::class, 'store']);
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'index']);
    Route::delete('users/{user}/permissions/{permission}', [UserPermissionController::class, 'destroy']);

    Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

});

// ==========================
// manage-permissions
// ==========================
Route::middleware(['auth:sanctum', 'permission:manage-permissions'])->group(function () {

    Route::apiResource('permissions', PermissionController::class);
});

// ==========================================================================================================
// ==========================================================================================================
// users routes
// ==========================================================================================================
// ==========================================================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('permission:view-users');

Route::post('users', [UserController::class, 'store'])->middleware('permission:view-users');
    

    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:view-users');

    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('permission:update-users');

    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users');
});


// ==========================================================================================================
// ==========================================================================================================
// dashboard routes
// ==========================================================================================================
// ==========================================================================================================

Route::middleware(['auth:sanctum', 'permission:view-dashboard'])->get('/dashboard/stats', [DashboardController::class, 'stats']);