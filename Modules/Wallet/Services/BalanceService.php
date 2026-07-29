<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Repositories\Interfaces\WalletRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletBalanceRepositoryInterface;
use Modules\Ledger\Services\LedgerService;
use Modules\Ledger\Services\BalanceService as LedgerBalanceService;

class BalanceService
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected WalletBalanceRepositoryInterface $balanceRepository,
        protected LedgerService $ledgerService,
        protected LedgerBalanceService $ledgerBalanceService,
    ) {
    }

    public function getWalletBalance(int|string $walletId): object
    {
        $wallet = $this->walletRepository->find($walletId);

        if (!$wallet) {
            throw new \InvalidArgumentException('Wallet not found.');
        }

        return $wallet->balance;
    }

    public function reconcileWalletBalance(int|string $walletId): object
    {
        $wallet = $this->walletRepository->find($walletId);

        if (!$wallet) {
            throw new \InvalidArgumentException('Wallet not found.');
        }

        $ledgerBalance = $this->ledgerBalanceService->calculateBalances($wallet->ledger_account_id);

        $balance = $this->balanceRepository->findByWalletId($walletId);

        $payload = [
            'wallet_id' => $walletId,
            'available_balance' => $ledgerBalance->available_balance,
            'blocked_balance' => $ledgerBalance->blocked_balance,
            'pending_balance' => $ledgerBalance->pending_balance,
            'total_balance' => $ledgerBalance->total_balance,
            'currency' => $ledgerBalance->currency,
        ];

        if ($balance) {
            $this->balanceRepository->update($balance->id, $payload);
            return $this->balanceRepository->findByWalletId($walletId);
        }

        return $this->balanceRepository->create(array_merge($payload, [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        ]));
    }
}
