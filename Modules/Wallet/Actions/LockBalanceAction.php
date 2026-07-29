<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletLockService;

class LockBalanceAction extends BaseAction
{
    public function __construct(protected WalletLockService $lockService)
    {
    }

    protected function handle(int|string $walletId, float $amount, string $reason, ?string $notes = null, \DateTime|string|null $expiresAt = null): object
    {
        return $this->lockService->lockBalance($walletId, $amount, $reason, $notes, $expiresAt);
    }
}
