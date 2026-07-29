<?php

namespace Modules\Wallet\Actions;

use Modules\Shared\Base\BaseAction;
use Modules\Wallet\Services\WalletService;

class TransferAction extends BaseAction
{
    public function __construct(protected WalletService $walletService)
    {
    }

    protected function handle(int|string $fromWalletId, int|string $toWalletId, float $amount, ?string $description = null, array $metadata = []): object
    {
        return $this->walletService->transfer($fromWalletId, $toWalletId, $amount, $description, $metadata);
    }
}
