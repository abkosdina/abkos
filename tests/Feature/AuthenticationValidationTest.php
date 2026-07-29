<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_validation_rejects_invalid_mobile_and_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Ali Rezaei',
            'mobile' => '1234567890',
            'email' => 'not-an-email',
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mobile', 'email', 'password']);
    }

    public function test_login_validation_requires_iranian_mobile_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'mobile' => '1234567890',
            'password' => '12345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mobile']);
    }

    public function test_register_without_email_creates_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Test User',
            'mobile' => '09130000006',
            'email' => null,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'mobile' => '09130000006',
            'name' => 'Test User',
            'email' => null,
        ]);
    }

    public function test_authenticated_user_can_retrieve_profile_via_auth_me(): void
    {
        $user = User::factory()->create([
            'name' => 'Dashboard User',
            'mobile' => '09130000007',
            'email' => 'dashboard@example.com',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}"
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([ 
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => 'Dashboard User',
                    'email' => 'dashboard@example.com',
                ],
            ]);
    }

    public function test_auth_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }
}
