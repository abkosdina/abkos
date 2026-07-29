<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentMetadata;
use Modules\Documents\Repositories\Interfaces\DocumentMetadataRepositoryInterface;

class DocumentMetadataRepository implements DocumentMetadataRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return DocumentMetadata::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentMetadata::query()->find($id, $columns);
    }

    public function create(array $data): object
    {
        return DocumentMetadata::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return DocumentMetadata::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return DocumentMetadata::query()->findOrFail($id)->delete();
    }

    public function getByDocument(int|string $documentId): array
    {
        return DocumentMetadata::query()->where('document_id', $documentId)->get()->all();
    }
}
