<?php

namespace Modules\Ledger\Repositories\Eloquent;

use Modules\Ledger\Models\Account;
use Modules\Ledger\Repositories\Interfaces\AccountRepositoryInterface;

class AccountRepository implements AccountRepositoryInterface
{
    public function __construct(protected Account $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->with('accountType', 'currentBalance')->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->with('accountType', 'currentBalance')->find($id, $columns);
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

    public function findByUuid(string $uuid, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('uuid', $uuid)->with('accountType', 'currentBalance')->first($columns);
    }

    public function getByOwner(string $ownerType, int|string $ownerId): array
    {
        return $this->model->query()->where('owner_type', $ownerType)->where('owner_id', $ownerId)->with('accountType', 'currentBalance')->get()->all();
    }
}
