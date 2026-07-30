<?php

namespace Modules\UserManagement\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Shared\Base\BaseController;
use Modules\UserManagement\Services\SidebarMenuService;
use App\Models\SiteSetting;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SidebarMenuController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            SidebarMenuService::ensureDefaultsExist();

            $config = SiteSetting::getValue('sidebar_menu_config', []);
            if (is_string($config)) {
                $config = json_decode($config, true) ?: [];
            }

            $roleKey = $this->resolveSidebarRoleKey();
            $menu = is_array($config) && array_key_exists($roleKey, $config) && is_array($config[$roleKey])
                ? $config[$roleKey]
                : SidebarMenuService::getBaseMenusForRole($roleKey);

            return response()->json([
                'success' => true,
                'data' => $menu,
            ]);
        } catch (\Throwable $e) {
            Log::error('SidebarMenuController@index error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری پیکربندی سایدبار.'], 500);
        }
    }

    public function me(): JsonResponse
    {
        return $this->index();
    }

    public function config(): JsonResponse
    {
        try {
            SidebarMenuService::ensureDefaultsExist();

            $config = SiteSetting::getValue('sidebar_menu_config', []);
            if (is_string($config)) {
                $config = json_decode($config, true) ?: [];
            }

            return response()->json([
                'success' => true,
                'data' => is_array($config) ? $config : [],
            ]);
        } catch (\Throwable $e) {
            Log::error('SidebarMenuController@config error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری پیکربندی کامل سایدبار.'], 500);
        }
    }

    private function resolveSidebarRoleKey(): string
    {
        $user = auth()->user();
        if (! $user) {
            return 'user';
        }

        $roleKeys = $user->getRoleNames()
            ->map(fn ($role) => SidebarMenuService::getCanonicalRoleKey($role))
            ->unique()
            ->toArray();

        if (in_array('super-admin', $roleKeys, true)) {
            return 'super-admin';
        }

        if (in_array('admin', $roleKeys, true)) {
            return 'admin';
        }

        if (in_array('bank-employee', $roleKeys, true)) {
            return 'bank-employee';
        }

        if (in_array('customer', $roleKeys, true)) {
            return 'customer';
        }

        if (in_array('operator', $roleKeys, true)) {
            return 'operator';
        }

        if (in_array('finance', $roleKeys, true)) {
            return 'finance';
        }

        return $roleKeys[0] ?? 'user';
    }

    /**
     * Get default base menus for all roles (used for initialization on frontend).
     */
    public function defaults(): JsonResponse
    {
        try {
            $defaults = SidebarMenuService::getAllRoleMenus();
            
            return response()->json([
                'success' => true,
                'data' => $defaults,
            ]);
        } catch (\Throwable $e) {
            Log::error('SidebarMenuController@defaults error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری منوهای پیش‌فرض.'], 500);
        }
    }

    /**
     * Get base menus for a specific role.
     */
    public function roleDefaults(string $roleKey): JsonResponse
    {
        try {
            $menus = SidebarMenuService::getBaseMenusForRole($roleKey);
            
            return response()->json([
                'success' => true,
                'data' => $menus,
            ]);
        } catch (\Throwable $e) {
            Log::error('SidebarMenuController@roleDefaults error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'خطای سرور هنگام بارگذاری منوهای نقش.'], 500);
        }
    }

    /**
     * Save/update sidebar menu configuration and sync permissions.
     */
    public function store(Request $request): JsonResponse
    {
        $config = $request->input('config', []);

        try {
            // Persist config and sync permissions per role
            DB::transaction(function () use ($config) {
                SiteSetting::setValue(
                    'sidebar_menu_config',
                    is_array($config) ? json_encode($config) : $config,
                    'Sidebar menu configuration',
                    'string'
                );

                // Helper to map frontend role keys to actual role names
                $mapRoleKeyToName = function ($key) {
                    $k = strtolower(trim($key));
                    return match ($k) {
                        'super-admin' => 'Super Admin',
                        'administrator', 'admin' => 'Admin',
                        'bank-employee' => 'Bank Employee',
                        'customer' => 'Customer',
                        'operator' => 'Operator',
                        'finance' => 'Finance',
                        default => ucfirst($k),
                    };
                };

                foreach ($config as $roleKey => $groups) {
                    $roleName = $mapRoleKeyToName($roleKey);
                    
                    // Auto-create missing roles to ensure permissions can be synced
                    $role = Role::firstOrCreate(
                        ['name' => $roleName],
                        ['guard_name' => 'web']
                    );

                    $permissionsToSync = [];

                    // Create permissions for groups and items
                    foreach ((array) $groups as $group) {
                        $groupId = $group['id'] ?? null;
                        if (!$groupId) {
                            continue;
                        }

                        // group-level permission
                        $groupPermName = "menu.{$groupId}";
                        $permissionsToSync[] = $groupPermName;
                        $groupPermission = Permission::firstOrNew(['name' => $groupPermName, 'guard_name' => 'web']);
                        $groupPermission->display_name = $group['label'] ?? $groupId;
                        $groupPermission->save();

                        $items = $group['items'] ?? [];
                        foreach ((array) $items as $item) {
                            $slug = preg_replace('/[^a-z0-9]+/i', '-', trim($item));
                            $permName = "menu.{$groupId}.{$slug}";
                            $permissionsToSync[] = $permName;
                            $permission = Permission::firstOrNew(['name' => $permName, 'guard_name' => 'web']);
                            $permission->display_name = $item;
                            $permission->save();
                        }
                    }

                    // Sync permissions on role
                    $role->syncPermissions(array_unique($permissionsToSync));
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'پیکربندی سایدبار ذخیره شد.',
                'data' => $config,
            ]);
        } catch (\Throwable $e) {
            Log::error('SidebarMenuController@store error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره پیکربندی سایدبار: ' . $e->getMessage(),
            ], 500);
        }
    }
}
