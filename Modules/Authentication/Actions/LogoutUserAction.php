<?php

namespace Modules\Authentication\Actions;

use Modules\Authentication\Services\SessionService;

class LogoutUserAction
{
    public function __construct(protected SessionService $service)
    {
    }

    public function execute(int $userId): void
    {
        $this->service->revokeAllForUser($userId);
    }
}
