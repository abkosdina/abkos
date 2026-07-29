<?php

namespace Modules\Wallet\Repositories\Eloquent;

use Modules\Wallet\Models\WalletLimit;
use Modules\Wallet\Repositories\Interfaces\WalletLimitRepositoryInterface;

class WalletLimitRepository implements WalletLimitRepositoryInterface
{
    public function __construct(protected WalletLimit $model)
    {
    }

    public function findByWalletId(int|string $walletId, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('wallet_id', $walletId)->first($columns);
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }
}
