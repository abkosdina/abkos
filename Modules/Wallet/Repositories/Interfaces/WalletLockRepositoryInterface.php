<?php

namespace Modules\Wallet\Repositories\Interfaces;

interface WalletLockRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getActiveLocks(int|string $walletId): array;

    public function release(int|string $id, array $data): bool;
}
