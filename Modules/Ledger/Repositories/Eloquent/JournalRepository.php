<?php

namespace Modules\Ledger\Repositories\Eloquent;

use Modules\Ledger\Models\JournalEntry;
use Modules\Ledger\Repositories\Interfaces\JournalRepositoryInterface;

class JournalRepository implements JournalRepositoryInterface
{
    public function __construct(protected JournalEntry $model)
    {
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->find($id, $columns);
    }

    public function getByTransactionId(int|string $transactionId): array
    {
        return $this->model->query()->where('ledger_transaction_id', $transactionId)->get()->all();
    }
}
