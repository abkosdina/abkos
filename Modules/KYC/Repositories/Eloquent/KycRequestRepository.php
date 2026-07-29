<?php

namespace Modules\KYC\Repositories\Eloquent;

use Modules\KYC\Models\KycRequest;
use Modules\KYC\Repositories\Interfaces\KycRequestRepositoryInterface;

class KycRequestRepository implements KycRequestRepositoryInterface
{
    public function __construct(protected KycRequest $model)
    {
    }

    public function all(array $columns = ['*']): array
    {
        return $this->model->query()
            ->with(['profile', 'documents', 'identitySnapshots', 'rejections', 'reviewLogs', 'statusHistory'])
            ->get($columns)
            ->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return $this->model->query()
            ->with(['profile', 'documents', 'identitySnapshots', 'rejections', 'reviewLogs', 'statusHistory'])
            ->find($id, $columns);
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

    public function getByUserId(int|string $userId): array
    {
        return $this->model->query()->where('user_id', $userId)->with(['profile', 'reviewLogs', 'statusHistory'])->get()->all();
    }

    public function findByUuid(string $uuid, array $columns = ['*']): ?object
    {
        return $this->model->query()->where('uuid', $uuid)->with(['profile', 'reviewLogs', 'statusHistory'])->first($columns);
    }
}
