<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentTag;
use Modules\Documents\Repositories\Interfaces\DocumentTagRepositoryInterface;

class DocumentTagRepository implements DocumentTagRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return DocumentTag::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentTag::query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return DocumentTag::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return DocumentTag::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return DocumentTag::query()->findOrFail($id)->delete();
    }
}
