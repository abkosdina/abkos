<?php

namespace Modules\Wallet\Repositories\Eloquent;

use Modules\Wallet\Models\WalletLock;
use Modules\Wallet\Repositories\Interfaces\WalletLockRepositoryInterface;

class WalletLockRepository implements WalletLockRepositoryInterface
{
    public function __construct(protected WalletLock $model)
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

    public function getActiveLocks(int|string $walletId): array
    {
        return $this->model->query()->where('wallet_id', $walletId)->where('status', 'locked')->get()->all();
    }

    public function release(int|string $id, array $data): bool
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }
}
