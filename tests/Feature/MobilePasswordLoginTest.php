<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class MobilePasswordLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_password_login_returns_token()
    {
        $user = User::factory()->create([
            'mobile' => '09134576502',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'mobile' => '09134576502',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'auth' => ['success', 'token']]);
    }
}
