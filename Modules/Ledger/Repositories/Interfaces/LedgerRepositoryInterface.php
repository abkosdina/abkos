<?php

namespace Modules\Ledger\Repositories\Interfaces;

interface LedgerRepositoryInterface
{
    public function all(array $columns = ['*']): array;

    public function createTransaction(array $data): object;

    public function findTransaction(int|string $id, array $columns = ['*']): ?object;

    public function createEntry(array $data): object;

    public function getEntriesByTransaction(int|string $transactionId): array;

    public function getEntriesByAccount(int|string $accountId): array;

    public function getBalancedEntries(array $entries): bool;
}
