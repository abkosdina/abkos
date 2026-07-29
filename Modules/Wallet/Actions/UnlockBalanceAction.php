<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletLockService;

class UnlockBalanceAction extends BaseAction
{
    public function __construct(protected WalletLockService $lockService)
    {
    }

    protected function handle(int|string $lockId): bool
    {
        return $this->lockService->unlockBalance($lockId);
    }
}
