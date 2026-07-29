<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrokerRegistrationToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_blocked_when_broker_registration_is_disabled(): void
    {
        SiteSetting::setValue('broker_registration_enabled', false, 'Disable broker registration', 'boolean');

        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Ali Broker',
            'mobile' => '09120000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonFragment([
                'message' => 'عضویت موقتا غیر فعال است. برای راهنمایی با راهبر سیستم تماس بگیرید.',
            ]);
    }
}
