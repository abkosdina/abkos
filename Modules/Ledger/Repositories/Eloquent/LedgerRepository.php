<?php

namespace Modules\Ledger\Repositories\Eloquent;

use Modules\Ledger\Models\LedgerEntry;
use Modules\Ledger\Models\LedgerTransaction;
use Modules\Ledger\Repositories\Interfaces\LedgerRepositoryInterface;

class LedgerRepository implements LedgerRepositoryInterface
{
    public function __construct(
        protected LedgerTransaction $transactionModel,
        protected LedgerEntry $entryModel,
    ) {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->transactionModel->query()->with('entries')->get($columns)->all();
    }

    public function createTransaction(array $data): object
    {
        return $this->transactionModel->query()->create($data);
    }

    public function findTransaction(int|string $id, array $columns = ['*']): ?object
    {
        return $this->transactionModel->query()->with('entries')->find($id, $columns);
    }

    public function createEntry(array $data): object
    {
        return $this->entryModel->query()->create($data);
    }

    public function getEntriesByTransaction(int|string $transactionId): array
    {
        return $this->entryModel->query()->where('ledger_transaction_id', $transactionId)->get()->all();
    }

    public function getEntriesByAccount(int|string $accountId): array
    {
        return $this->entryModel->query()
            ->where('account_id', $accountId)
            ->orderBy('created_at')
            ->get()
            ->all();
    }

    public function getBalancedEntries(array $entries): bool
    {
        $debit = array_sum(array_map(fn ($entry) => (float) ($entry['debit'] ?? 0), $entries));
        $credit = array_sum(array_map(fn ($entry) => (float) ($entry['credit'] ?? 0), $entries));

        return bccomp((string) $debit, (string) $credit, 8) === 0;
    }
}
