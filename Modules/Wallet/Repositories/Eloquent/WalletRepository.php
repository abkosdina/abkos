<?php

namespace Modules\Wallet\Repositories\Eloquent;

use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Repositories\Interfaces\WalletRepositoryInterface;

class WalletRepository implements WalletRepositoryInterface
{
    public function __construct(protected Wallet $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->with('balance', 'transactions', 'locks', 'limits')->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->with('balance', 'transactions', 'locks', 'limits')->find($id, $columns);
    }

    public function findByUserId(int|string $userId, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('user_id', $userId)->with('balance', 'transactions', 'locks', 'limits')->first($columns);
    }

    public function create(array $data): object
    {
        return $this->model->query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->model->query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return $this->model->query()->findOrFail($id)->delete();
    }
}
