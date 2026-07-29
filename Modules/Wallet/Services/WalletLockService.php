<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Repositories\Interfaces\WalletLockRepositoryInterface;
use Modules\Wallet\Repositories\Interfaces\WalletBalanceRepositoryInterface;

class WalletLockService
{
    public function __construct(
        protected WalletLockRepositoryInterface $lockRepository,
        protected WalletBalanceRepositoryInterface $balanceService,
    ) {
    }

    public function lockBalance(int|string $walletId, float $amount, string $reason, ?string $notes = null, \DateTime|string|null $expiresAt = null): object
    {
        $lock = $this->lockRepository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'wallet_id' => $walletId,
            'reason' => $reason,
            'amount' => $amount,
            'expires_at' => $expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt) : null,
            'notes' => $notes,
            'status' => 'locked',
        ]);

        return $lock;
    }

    public function unlockBalance(int|string $lockId): bool
    {
        return $this->lockRepository->release($lockId, [
            'status' => 'released',
            'released_at' => now(),
        ]);
    }
}
