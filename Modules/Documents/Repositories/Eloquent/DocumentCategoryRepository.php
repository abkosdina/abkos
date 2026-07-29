<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Repositories\Interfaces\DocumentCategoryRepositoryInterface;

class DocumentCategoryRepository implements DocumentCategoryRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return DocumentCategory::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentCategory::query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return DocumentCategory::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return DocumentCategory::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return DocumentCategory::query()->findOrFail($id)->delete();
    }
}
