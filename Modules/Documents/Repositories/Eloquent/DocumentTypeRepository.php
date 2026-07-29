<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentType;
use Modules\Documents\Repositories\Interfaces\DocumentTypeRepositoryInterface;

class DocumentTypeRepository implements DocumentTypeRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return DocumentType::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentType::query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return DocumentType::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return DocumentType::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return DocumentType::query()->findOrFail($id)->delete();
    }
}
