<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletService;

class CreateWalletAction extends BaseAction
{
    public function __construct(protected WalletService $walletService)
    {
    }

    protected function handle(array $data): object
    {
        return $this->walletService->createWallet($data);
    }
}
