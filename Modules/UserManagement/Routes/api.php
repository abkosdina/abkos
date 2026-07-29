<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\Http\Controllers\DashboardController;
use Modules\UserManagement\Http\Controllers\SidebarMenuController;
use Modules\UserManagement\Http\Controllers\UserController;

Route::prefix('api/v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->middleware(['permission:menu.users']);
        Route::post('/users', [UserController::class, 'store'])->middleware(['permission:menu.users']);
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware(['permission:menu.users']);
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware(['permission:menu.users']);
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(['permission:menu.users']);
        Route::post('/users/{user}/moderate', [UserController::class, 'moderate'])->middleware(['permission:menu.users']);
        Route::get('/user-management/roles', [UserController::class, 'roles'])->middleware(['permission:menu.users']);
        Route::get('/user-management/permissions', [UserController::class, 'permissions'])->middleware(['permission:menu.users']);
        
        // Sidebar menus
        Route::get('/user-management/sidebar-menus', [SidebarMenuController::class, 'index'])->middleware(['permission:menu.users']);
        Route::get('/user-management/sidebar-menus/me', [SidebarMenuController::class, 'me']);
        Route::get('/user-management/sidebar-menus/defaults', [SidebarMenuController::class, 'defaults'])->middleware(['permission:menu.users']);
        Route::get('/user-management/sidebar-menus/defaults/{roleKey}', [SidebarMenuController::class, 'roleDefaults'])->middleware(['permission:menu.users']);
        Route::get('/dashboard/config', [DashboardController::class, 'config']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/activity', [DashboardController::class, 'activity']);
        
        // Allow role-based access to save sidebar config so Super Admin can create permissions
        Route::post('/user-management/sidebar-menus', [SidebarMenuController::class, 'store'])->middleware(['role:Super Admin']);
    });

    // Locations: provinces and cities
    Route::prefix('locations')->group(function () {
        Route::get('/provinces', [\App\Http\Controllers\LocationController::class, 'getProvinces']);
        Route::get('/provinces/{province}/cities', [\App\Http\Controllers\LocationController::class, 'getCitiesByProvince']);
    });
});
