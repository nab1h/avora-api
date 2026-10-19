<?php

use App\Http\Controllers\Admin\AnalyticsDashboardController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\SeoPageController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SiteScriptController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogGalleryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\SocialPlatformController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserPermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PublicSiteScriptController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Catalog reads are public and expose active records only. Existing routes
// retain their own response contracts and authorization middleware.
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show'])->whereNumber('category');
Route::get('categories/{category}/products', [ProductController::class, 'categoryProducts'])->whereNumber('category');
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show'])->whereNumber('product');
Route::get('products/{product}/images', [ProductImageController::class, 'index'])->whereNumber('product');

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('admin/categories', [CategoryController::class, 'adminIndex']);
    Route::get('admin/categories/{category}', [CategoryController::class, 'adminShow'])->whereNumber('category');
    Route::get('admin/products', [ProductController::class, 'adminIndex']);
    Route::get('admin/products/{product}', [ProductController::class, 'adminShow'])->whereNumber('product');
    Route::apiResource('admin/categories', CategoryController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('admin/products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->whereNumber('product');
    Route::patch('products/{product}/images/{image}', [ProductImageController::class, 'update'])->scopeBindings()->whereNumber(['product', 'image']);
    Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->scopeBindings()->whereNumber(['product', 'image']);
    Route::apiResource('admin/galleries', CatalogGalleryController::class);
});

// Dashboard

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])
    ->get('/dashboard/stats', [DashboardController::class, 'stats']);

// Authentication

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Password management

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword'])
    ->middleware('auth:sanctum');

// Profile

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});

Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

// Role management

Route::middleware(['auth:sanctum', 'permission:manage-settings'])->group(function () {
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

// Permission management

Route::middleware(['auth:sanctum', 'permission:manage-settings'])->group(function () {
    Route::apiResource('permissions', PermissionController::class);
});

// Users

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('permission:manage-settings');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:manage-settings');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:manage-settings');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('permission:manage-settings');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:manage-settings');
});

// Social links and platforms

Route::get('social-links', [SocialLinkController::class, 'index']);
Route::get('social-platforms', [SocialPlatformController::class, 'index']);

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('admin/social-links', [SocialLinkController::class, 'adminIndex']);
    Route::post('social-links', [SocialLinkController::class, 'store']);
    Route::get('social-links/{socialLink}', [SocialLinkController::class, 'show']);
    Route::put('social-links/{socialLink}', [SocialLinkController::class, 'update']);
    Route::delete('social-links/{socialLink}', [SocialLinkController::class, 'destroy']);
});

// Services

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('admin/services', [ServiceController::class, 'adminIndex']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::get('services/{service}', [ServiceController::class, 'show']);
    Route::put('services/{service}', [ServiceController::class, 'update']);
    Route::delete('services/{service}', [ServiceController::class, 'destroy']);
});

Route::get('services', [ServiceController::class, 'index']);

// Gallery

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('admin/gallery', [GalleryController::class, 'adminIndex']);
    Route::post('gallery', [GalleryController::class, 'store']);
    Route::delete('gallery/bulk-delete', [GalleryController::class, 'bulkDestroy']);
    Route::delete('gallery/{galleryItem}', [GalleryController::class, 'destroy']);
});

// ============================================================================
// ============================================================================
// website settings
// ===========================================================================
// ==========================================================================

Route::get('/settings', [SettingsController::class, 'index']);
Route::get('/seo/{page}', [SeoController::class, 'show']);
Route::get('/contents/{page}', [ContentController::class, 'show']);

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->group(function () {
    Route::get('/admin/settings', [AdminSettingsController::class, 'index']);
    Route::post('/admin/settings', [AdminSettingsController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->prefix('admin')->group(function () {
    Route::apiResource('seo-pages', SeoPageController::class);
});

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->prefix('admin')->group(function () {
    Route::get('/contents/pages', [AdminContentController::class, 'pages']);
    Route::post('/contents', [AdminContentController::class, 'store']);
    Route::get('/contents', [AdminContentController::class, 'index']);
    Route::get('/contents/{content}', [AdminContentController::class, 'show'])->whereNumber('content');
    Route::put('/contents/{content}', [AdminContentController::class, 'update'])->whereNumber('content');
    Route::delete('/contents/{content}', [AdminContentController::class, 'destroy'])->whereNumber('content');
});

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])->prefix('admin')->group(function () {
    Route::apiResource('site-scripts', SiteScriptController::class);
});

Route::get('/site-scripts/public', [PublicSiteScriptController::class, 'index']);

// Analytics Events
Route::post('/analytics/events', [AnalyticsController::class, 'store'])->middleware('throttle:30,1');

Route::middleware(['auth:sanctum', 'active', 'permission:manage-settings'])
    ->prefix('admin')
    ->group(function () {
        Route::get(
            '/analytics',
            [AnalyticsDashboardController::class, 'index']
        );
    });
