<?php

namespace Modules\KYC\Repositories\Eloquent;

use Modules\KYC\Models\KycProfile;
use Modules\KYC\Repositories\Interfaces\KycProfileRepositoryInterface;

class KycProfileRepository implements KycProfileRepositoryInterface
{
    public function __construct(protected KycProfile $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()->find($id, $columns);
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

    public function findByUserId(int|string $userId, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('user_id', $userId)->first($columns);
    }
}
