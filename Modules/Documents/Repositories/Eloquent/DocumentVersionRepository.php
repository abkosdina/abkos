<?php

namespace Modules\Documents\Repositories\Eloquent;

use Modules\Documents\Models\DocumentVersion;
use Modules\Documents\Repositories\Interfaces\DocumentVersionRepositoryInterface;

class DocumentVersionRepository implements DocumentVersionRepositoryInterface
{
    public function all(array $columns = ['*']): array
    {
        return DocumentVersion::query()->get($columns)->all();
    }

    public function find(int|string $id, array $columns = ['*']): ?object
    {
        return DocumentVersion::query()->find($id, $columns);
    }

    public function findByDocumentId(int|string $documentId, array $columns = ['*']): array
    {
        return DocumentVersion::query()->where('document_id', $documentId)->get($columns)->all();
    }

    public function create(array $data): object
    {
        return DocumentVersion::query()->create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return DocumentVersion::query()->findOrFail($id)->update($data);
    }

    public function delete(int|string $id): bool
    {
        return DocumentVersion::query()->findOrFail($id)->delete();
    }

    public function getByDocument(int|string $documentId): array
    {
        return $this->findByDocumentId($documentId);
    }

    public function findLatestVersion(int|string $documentId): ?object
    {
        return DocumentVersion::query()
            ->where('document_id', $documentId)
            ->orderByDesc('created_at')
            ->first();
    }
}
