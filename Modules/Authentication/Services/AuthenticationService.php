<?php

namespace Modules\Authentication\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Authentication\Repositories\Interfaces\OtpRepositoryInterface;
use Modules\Authentication\Repositories\Interfaces\SessionRepositoryInterface;
use Modules\Authentication\Repositories\Interfaces\LoginHistoryRepositoryInterface;
use Modules\Authentication\Services\SecurityService;

class AuthenticationService
{
    public function __construct(
        protected OtpRepositoryInterface $otpRepository,
        protected SessionRepositoryInterface $sessionRepository,
        protected LoginHistoryRepositoryInterface $loginHistoryRepository,
        protected SecurityService $securityService,
    ) {
    }

    public function login(User $user, string $ipAddress, ?string $userAgent = null): array
    {
        $token = $user->createToken('auth-token')->plainTextToken;
        $this->sessionRepository->create([
            'user_id' => $user->id,
            'token_id' => Str::uuid()->toString(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->loginHistoryRepository->create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'device' => $userAgent,
            'browser' => 'unknown',
            'operating_system' => 'unknown',
            'login_time' => now(),
            'logout_time' => null,
        ]);

        return ['success' => true, 'token' => $token];
    }
}
