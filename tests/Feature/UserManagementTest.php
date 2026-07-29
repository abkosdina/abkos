<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_endpoint_is_available_for_authorized_user(): void
    {
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'menu.users', 'guard_name' => 'web']);
        $user->givePermissionTo('menu.users');

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/users');

        $response->assertStatus(200);
    }
}
