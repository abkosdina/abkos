<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletService;

class WithdrawAction extends BaseAction
{
    public function __construct(protected WalletService $walletService)
    {
    }

    protected function handle(int|string $walletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->walletService->withdraw($walletId, $amount, $description, $metadata);
    }
}
