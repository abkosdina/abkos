<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SidebarPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_protected_returns_403_for_users_without_permission()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_sidebar_save_creates_permissions_and_syncs_role()
    {
        // create role and user
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $payload = [
            'super-admin' => [
                ['id' => 'ads', 'items' => ['همه آگهی‌ها', 'تأیید آگهی‌ها']],
                ['id' => 'users', 'items' => ['مدیران']],
            ],
        ];

        // call store endpoint to save config and create/sync permissions
        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/user-management/sidebar-menus', ['config' => $payload])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // permission records exist
        $this->assertDatabaseHas('permissions', ['name' => 'menu.ads']);

        // role has permission
        $this->assertTrue($user->hasPermissionTo('menu.ads'));

        // protected route now accessible
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertStatus(200);
    }

    public function test_authenticated_user_can_fetch_personal_sidebar_menu_without_menu_users_permission()
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user-management/sidebar-menus/me')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $menu = $response->json('data');
        $this->assertIsArray($menu);
        $ids = array_column($menu, 'id');
        $this->assertContains('dashboard', $ids);
        $this->assertNotContains('users', $ids);
    }
    public function test_sidebar_menu_endpoint_returns_current_user_menu_only()
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user-management/sidebar-menus')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $menu = $response->json('data');
        $this->assertIsArray($menu);
        $ids = array_column($menu, 'id');
        $this->assertContains('dashboard', $ids);
        $this->assertNotContains('users', $ids);
    }

    public function test_admin_sidebar_menu_endpoint_returns_admin_menu_only()
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $adminUser = User::factory()->create();
        $adminUser->assignRole('Admin');

        $payload = [
            'super-admin' => [
                ['id' => 'dashboard', 'items' => ['Overview']],
            ],
            'admin' => [
                ['id' => 'ads', 'items' => ['All Ads']],
            ],
        ];

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/user-management/sidebar-menus', ['config' => $payload])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $response = $this->actingAs($adminUser, 'sanctum')
            ->getJson('/api/v1/user-management/sidebar-menus')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSame($payload['admin'], $response->json('data'));
    }

    public function test_sidebar_menu_config_requires_menu_users_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user-management/sidebar-menus/config')
            ->assertStatus(403);
    }}
