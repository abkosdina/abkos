<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\Document;
use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;

class DocumentRepository implements DocumentRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return Document::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return Document::query()->find($id, $columns);
    }

    public function findByUuid(string $uuid, array $columns = ['*']): ?object
    {
        return Document::query()->where('uuid', $uuid)->first($columns);
    }

    public function create(array $data): object
    {
        return Document::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return Document::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return Document::query()->findOrFail($id)->delete();
    }

    public function getByOwner(string $ownerType, int|string $ownerId): array
    {
        return Document::query()->where('owner_type', $ownerType)->where('owner_id', $ownerId)->get()->all();
    }

    public function findByHash(string $hash): ?object
    {
        return Document::query()->where('hash', $hash)->first();
    }
}
