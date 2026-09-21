<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\SocialPlatformController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('social-links', [SocialLinkController::class, 'index']);
// ==========================
// auth routes
// ==========================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

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

// ==========================================================================================================
// ==========================================================================================================
// PROMSSIONS AND ROLES ROUTES
// ==========================================================================================================
// ==========================================================================================================

// ==========================
// manage-roles
// ==========================

Route::middleware(['auth:sanctum', 'permission:manage-roles'])->group(function () {

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

// ==========================================================================================================
// ==========================================================================================================
// Settings Routes
// ==========================================================================================================
// ==========================================================================================================

Route::middleware(['auth:sanctum', 'permission:manage-roles'])->group(function () {

    Route::get('admin/social-links', [SocialLinkController::class, 'adminIndex']);
    Route::post('social-links', [SocialLinkController::class, 'store']);
    Route::get('social-links/{socialLink}', [SocialLinkController::class, 'show']);
    Route::put('social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::delete('social-links/{socialLink}', [SocialLinkController::class, 'destroy']);

});

Route::get('social-links', [SocialLinkController::class, 'index']);
Route::get('social-platforms', [SocialPlatformController::class, 'index']);

// ==========================================================================================================
Route::middleware(['auth:sanctum', 'permission:manage-roles'])->group(function () {
    Route::get('admin/services', [ServiceController::class,'adminIndex',]);
    Route::post('services', [ServiceController::class,'store',]);
    Route::get('services/{service}', [ServiceController::class,'show',]);
    Route::put('services/{service}', [ServiceController::class,'update',]);
    Route::delete('services/{service}', [ServiceController::class,'destroy',]);
});

Route::get('services', [ServiceController::class,'index']);
// ==========================================================================================================

Route::middleware('auth:sanctum')->group(function () {

    Route::get('admin/gallery', [GalleryController::class, 'adminIndex']);

    Route::post('gallery', [GalleryController::class, 'store']);

    Route::delete('gallery/bulk-delete', [GalleryController::class, 'bulkDestroy']);

    Route::delete('gallery/{galleryItem}', [GalleryController::class, 'destroy']);

});