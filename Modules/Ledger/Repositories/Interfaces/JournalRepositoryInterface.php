<?php

namespace Modules\Ledger\Repositories\Interfaces;

interface JournalRepositoryInterface
{
    public function create(array $data): object;

    public function find(int|string $id, array $columns = ['*']): ?object;

    public function getByTransactionId(int|string $transactionId): array;
}
