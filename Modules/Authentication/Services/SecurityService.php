<?php

namespace Modules\Authentication\Services;

use Illuminate\Support\Facades\RateLimiter;

class SecurityService
{
    public function checkRateLimit(string $ipAddress, string $mobile): void
    {
        $key = 'otp:' . md5($ipAddress . ':' . $mobile);
        $attempts = RateLimiter::attempts($key);

        if ($attempts >= 5) {
            abort(429, 'Too many OTP requests.');
        }

        RateLimiter::hit($key, 60);
    }
}
