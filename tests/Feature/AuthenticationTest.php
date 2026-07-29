<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Authentication\Models\OtpRequest;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_request_endpoint_is_available(): void
    {
        $response = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000']);

        $response->assertStatus(200);
    }

    public function test_otp_request_creates_record_with_uuid(): void
    {
        $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000001']);

        $otpRequest = OtpRequest::query()->latest()->first();

        $this->assertNotNull($otpRequest);
        $this->assertNotNull($otpRequest->uuid);
        $this->assertTrue(Str::isUuid($otpRequest->uuid));
    }
}
