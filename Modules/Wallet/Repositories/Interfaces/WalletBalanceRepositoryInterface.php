<?php

namespace Modules\Wallet\Repositories\Interfaces;

interface WalletBalanceRepositoryInterface
{
    public function findByWalletId(int|string $walletId, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;
}
