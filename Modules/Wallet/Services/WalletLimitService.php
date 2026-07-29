<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Repositories\Interfaces\WalletLimitRepositoryInterface;

class WalletLimitService
{
    public function __construct(protected WalletLimitRepositoryInterface $limitRepository)
    {
    }

    public function getLimits(int|string $walletId): ?object
    {
        return $this->limitRepository->findByWalletId($walletId);
    }

    public function updateLimits(int|string $walletId, array $data): object
    {
        $limit = $this->limitRepository->findByWalletId($walletId);

        if (!$limit) {
            return $this->limitRepository->create(array_merge($data, [
                'uuid' => \Illuminate\Support\Str::uuid()->toString(),
                'wallet_id' => $walletId,
            ]));
        }

        $this->limitRepository->update($limit->id, $data);

        return $this->limitRepository->findByWalletId($walletId);
    }
}
