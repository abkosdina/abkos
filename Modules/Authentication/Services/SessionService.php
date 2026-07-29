<?php

namespace Modules\Authentication\Services;

use Modules\Authentication\Repositories\Interfaces\SessionRepositoryInterface;
use Modules\Authentication\Repositories\Interfaces\LoginHistoryRepositoryInterface;

class SessionService
{
    public function __construct(
        protected SessionRepositoryInterface $sessionRepository,
        protected LoginHistoryRepositoryInterface $loginHistoryRepository,
    ) {
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->sessionRepository->revokeAllForUser($userId);
    }
}
