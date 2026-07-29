<?php

namespace Modules\Authentication\Actions;

use App\Models\User;
use Modules\Authentication\Services\AuthenticationService;

class LoginUserAction
{
    public function __construct(protected AuthenticationService $service)
    {
    }

    public function execute(User $user, string $ipAddress, ?string $userAgent = null): array
    {
        return $this->service->login($user, $ipAddress, $userAgent);
    }
}
