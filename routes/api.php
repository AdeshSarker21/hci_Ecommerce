<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
]));

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle.login');
});

// Protected auth routes
Route::middleware(['auth:api', 'ensure.active'])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);
});

// Admin role/permission management
Route::middleware(['auth:api', 'ensure.active', 'check.role:super-admin,admin'])->prefix('admin')->group(function () {
    Route::get('/roles', [RolePermissionController::class, 'indexRoles']);
    Route::post('/roles', [RolePermissionController::class, 'storeRole']);
    Route::get('/roles/{role}', [RolePermissionController::class, 'showRole']);
    Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole']);
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroyRole']);

    Route::get('/permissions', [RolePermissionController::class, 'indexPermissions']);
    Route::post('/permissions', [RolePermissionController::class, 'storePermission']);
    Route::put('/permissions/{permission}', [RolePermissionController::class, 'updatePermission']);
    Route::delete('/permissions/{permission}', [RolePermissionController::class, 'destroyPermission']);

    Route::post('/assign-role', [RolePermissionController::class, 'assignRole']);
    Route::post('/remove-role', [RolePermissionController::class, 'removeRole']);
});
