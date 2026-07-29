<?php

namespace Modules\Wallet\Repositories\Interfaces;

interface WalletTransactionRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function filterByWallet(int|string $walletId, array $filters = []): array;
}
