<?php

return [
    'otp' => [
        'length' => 6,
        'ttl' => env('OTP_TTL', 300),
        'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),
    ],
    'rate_limit' => [
        'requests' => env('AUTH_RATE_LIMIT_REQUESTS', 5),
        'minutes' => env('AUTH_RATE_LIMIT_MINUTES', 1),
    ],
    'session' => [
        'token_expiration_days' => env('SANCTUM_TOKEN_EXPIRATION_DAYS', 30),
    ],
];
