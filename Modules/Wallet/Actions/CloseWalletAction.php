<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletService;

class CloseWalletAction extends BaseAction
{
    public function __construct(protected WalletService $walletService)
    {
    }

    protected function handle(int|string $walletId): object
    {
        return $this->walletService->updateWalletStatus($walletId, config('wallet.statuses.closed'));
    }
}
