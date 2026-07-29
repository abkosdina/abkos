<?php

namespace Modules\Ledger\Repositories\Eloquent;

use Modules\Ledger\Models\AccountBalance;
use Modules\Ledger\Models\BalanceSnapshot;
use Modules\Ledger\Repositories\Interfaces\BalanceRepositoryInterface;

class BalanceRepository implements BalanceRepositoryInterface
{
    public function __construct(
        protected AccountBalance $balanceModel,
        protected BalanceSnapshot $snapshotModel,
    ) {
    }

    public function findByAccountId(int|string $accountId, array $columns = ['*']): ?object
    {
        return $this->balanceModel->query()->where('account_id', $accountId)->first($columns);
    }

    public function create(array $data): object
    {
        return $this->balanceModel->query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->balanceModel->query()->findOrFail($id)->update($data);
    }

    public function snapshot(array $data): object
    {
        return $this->snapshotModel->query()->create($data);
    }

    public function getSnapshotsForAccount(int|string $accountId): array
    {
        return $this->snapshotModel->query()->where('account_id', $accountId)->get()->all();
    }
}
