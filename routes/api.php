<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\InvitationController;
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

// Authentication and account access

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Google authentication
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);

// Facebook authentication
Route::get('/auth/facebook/redirect', [AuthController::class, 'facebookRedirect']);
Route::get('/auth/facebook/callback', [AuthController::class, 'facebookCallback']);

// Profile management
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});

// Password management
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

// Email verification
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware(['auth:sanctum', 'throttle:20,60']);

// Roles, permissions, and invitations

Route::middleware(['auth:sanctum', 'permission:manage-roles', 'verified', 'active'])->group(function () {

    // Role management
    Route::apiResource('roles', RoleController::class);

    // Role permissions
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'store']);
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index']);
    Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'destroy']);

    // User roles
    Route::post('users/{user}/roles', [UserRoleController::class, 'store']);
    Route::get('users/{user}/roles', [UserRoleController::class, 'index']);
    Route::delete('users/{user}/roles/{role}', [UserRoleController::class, 'destroy']);

    // User permissions
    Route::post('users/{user}/permissions', [UserPermissionController::class, 'store']);
    Route::get('users/{user}/permissions', [UserPermissionController::class, 'index']);
    Route::delete('users/{user}/permissions/{permission}', [UserPermissionController::class, 'destroy']);

    Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

    // Invitation management
    Route::post('/invitations', [InvitationController::class, 'store']);
    Route::post('/invitations/accept', [InvitationController::class, 'accept']);
    Route::get('/invitations', [InvitationController::class, 'index']);
    Route::patch('/invitations/{invitation}/revoke', [InvitationController::class,'revoke']);
    Route::post('/invitations/{invitation}/resend', [InvitationController::class,'resend']);

});

// Permission management
Route::middleware(['auth:sanctum', 'permission:manage-permissions'])->group(function () {

    Route::apiResource('permissions', PermissionController::class);
});

// User management
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('permission:view-users');

    Route::post('users', [UserController::class, 'store'])->middleware('permission:view-users');

    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:view-users');

    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('permission:update-users');

    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users');
});

// Dashboard

Route::middleware(['auth:sanctum', 'permission:view-dashboard'])->get('/dashboard/stats', [DashboardController::class, 'stats']);


// Social links and platform settings

Route::middleware(['auth:sanctum', 'permission:manage-roles'])->group(function () {

    Route::get('admin/social-links', [SocialLinkController::class, 'adminIndex']);
    Route::post('social-links', [SocialLinkController::class, 'store']);
    Route::get('social-links/{socialLink}', [SocialLinkController::class, 'show']);
    Route::put('social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::delete('social-links/{socialLink}', [SocialLinkController::class, 'destroy']);

});

Route::get('social-links', [SocialLinkController::class, 'index']);
Route::get('social-platforms', [SocialPlatformController::class, 'index']);

// Service management
Route::middleware(['auth:sanctum', 'permission:manage-roles'])->group(function () {
    Route::get('admin/services', [ServiceController::class,'adminIndex',]);
    Route::post('services', [ServiceController::class,'store',]);
    Route::get('services/{service}', [ServiceController::class,'show',]);
    Route::put('services/{service}', [ServiceController::class,'update',]);
    Route::delete('services/{service}', [ServiceController::class,'destroy',]);
});

Route::get('services', [ServiceController::class,'index']);

// Gallery management
Route::middleware('auth:sanctum')->group(function () {

    Route::get('admin/gallery', [GalleryController::class, 'adminIndex']);

    Route::post('gallery', [GalleryController::class, 'store']);

    Route::delete('gallery/bulk-delete', [GalleryController::class, 'bulkDestroy']);

    Route::delete('gallery/{galleryItem}', [GalleryController::class, 'destroy']);

});