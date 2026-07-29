<?php

namespace Modules\Ledger\Repositories\Interfaces;

interface BalanceRepositoryInterface
{
    public function findByAccountId(int|string $accountId, array $columns = ['*']): ?object;

    public function create(array $data): object;

    public function update(int|string $id, array $data): bool;

    public function snapshot(array $data): object;

    public function getSnapshotsForAccount(int|string $accountId): array;
}
